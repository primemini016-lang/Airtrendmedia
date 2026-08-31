<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Services\SettingService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home(SettingService $settings)
    {
        $s = $settings->all();
        $liveTasks = Task::with('category', 'user:id,username,name,image')
            ->where('status', 1)->whereColumn('booked', '<', 'amount')->latest()->limit(8)->get();
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();
        return view('public.home', compact('s', 'liveTasks', 'categories'));
    }

    public function browse(Request $request)
    {
        $query = Task::with('category', 'user:id,username,name,image')
            ->where('status', 1)->whereColumn('booked', '<', 'amount');

        if ($cat = $request->input('category')) {
            $query->where('category_id', $cat);
        }
        if ($q = $request->input('q')) {
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")->orWhere('details', 'like', "%{$q}%");
            });
        }
        $tasks = $query->latest()->paginate(18)->appends($request->query());
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();
        return view('public.browse', compact('tasks', 'categories'));
    }

    public function faqs()
    {
        $faqs = Faq::orderBy('position')->get();
        return view('public.faqs', compact('faqs'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:191',
            'message' => 'required|string|max:5000',
        ]);
        // In production this would email the admin; we simply acknowledge.
        return back()->with('success', 'Thank you! Your message has been received. We will get back to you shortly.');
    }

    public function affiliate(SettingService $settings, \Illuminate\Http\Request $request)
    {
        // Logged-in users already have an affiliate dashboard; never ask them to sign up again.
        if (auth('web')->check()) {
            return redirect()->route('user.affiliate');
        }

        if ($ref = $request->query('ref')) {
            \Illuminate\Support\Facades\Cookie::queue(
                \Illuminate\Support\Facades\Cookie::make(
                    'airtrend_ref', trim($ref), 60 * 24 * 120, '/', null, $request->isSecure(), true, false, 'lax'
                )
            );
        }
        $s = $settings->all();
        return view('public.affiliate', compact('s'));
    }

    public function terms()
    {
        return view('public.terms');
    }
}
