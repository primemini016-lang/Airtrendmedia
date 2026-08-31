<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\AiService;
use App\Services\PaystackService;
use App\Models\Currency;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class AiController extends Controller {
 public function text(Request $request,AiService $ai){ $v=$request->validate(['prompt'=>'required|string|max:12000','max_tokens'=>'nullable|integer|min:200|max:8000']); try{return response()->json(['ok'=>true,'text'=>$ai->text(auth('web')->user(),$v['prompt'],$v['max_tokens']??2000)]);}catch(\Throwable $e){return response()->json(['ok'=>false,'error'=>$e->getMessage()],422);} }
 public function purchaseCredits(Request $request, PaystackService $paystack){
  $v=$request->validate(['amount'=>'required|numeric|min:1|max:10000']); $user=auth('web')->user();
  if(!$paystack->configured()) return back()->with('error','Paystack is not configured by the administrator.');
  $currency=Currency::where('paystack_supported',true)->where('active',true)->orderByDesc('is_default')->first();
  if(!$currency) return back()->with('error','No Paystack-supported currency is configured.');
  $amountUsd=(float)$v['amount']; $charge=round($currency->fromUsd($amountUsd),2); $ref=$paystack->generateReference('AIC');
  $dep=Deposit::create(['user_id'=>$user->id,'method_id'=>null,'amount'=>$amountUsd,'amount_paid'=>$charge,'type'=>'ai_credits','reference'=>$ref,'manual'=>false,'note'=>'AI credit purchase','status'=>0,'date'=>now()->toDateString()]);
  $payload=$paystack->initialise(['email'=>$user->email,'amount'=>(int)round($charge*100),'currency'=>$currency->code,'reference'=>$ref,'callback_url'=>route('user.ai.credits.verify'),'metadata'=>['deposit_id'=>$dep->id,'user_id'=>$user->id,'type'=>'ai_credits','amount_usd'=>$amountUsd]]);
  if(!$payload || empty($payload['authorization_url'])) { $dep->update(['status'=>2,'reject_note'=>'Payment gateway initialisation failed']); return back()->with('error','Unable to start AI credit payment. No balance was charged.'); }
  return redirect()->away($payload['authorization_url']);
 }
 public function verifyCredits(Request $request, PaystackService $paystack){
  $ref=$request->query('reference'); if(!$ref) return redirect()->route('user.dashboard')->with('error','No AI credit payment reference was returned.');
  $dep=Deposit::where('reference',$ref)->where('type','ai_credits')->first(); if(!$dep) return redirect()->route('user.dashboard')->with('error','AI credit payment record not found.');
  if($dep->isPaid()) return redirect()->route('user.dashboard')->with('info','AI credits were already added.');
  $data=$paystack->verify($ref); if(!$data) return redirect()->route('user.dashboard')->with('error','AI credit payment could not be verified. No credits were added.');
  DB::transaction(function()use($dep){ $dep->update(['status'=>1]); DB::table('ai_credit_wallets')->insertOrIgnore(['user_id'=>$dep->user_id,'credits'=>0,'created_at'=>now(),'updated_at'=>now()]); DB::table('ai_credit_wallets')->where('user_id',$dep->user_id)->increment('credits',(float)$dep->amount); DB::table('ai_credit_transactions')->insert(['user_id'=>$dep->user_id,'credits'=>$dep->amount,'type'=>'purchase','reference'=>$dep->reference,'description'=>'AI credit purchase','provider'=>'paystack','created_at'=>now(),'updated_at'=>now()]); });
  return redirect()->route('user.dashboard')->with('success', money((float)$dep->amount).' of AI credits added successfully.');
 }
 public function image(Request $request,AiService $ai){$v=$request->validate(['prompt'=>'required|string|max:4000']);try{return response()->json(['ok'=>true,'url'=>$ai->image(auth('web')->user(),$v['prompt'])]);}catch(\Throwable $e){return response()->json(['ok'=>false,'error'=>$e->getMessage()],422);}}
}
