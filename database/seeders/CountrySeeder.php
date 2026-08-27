<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['United States', 'US', '+1'], ['Canada', 'CA', '+1'], ['United Kingdom', 'GB', '+44'],
            ['Nigeria', 'NG', '+234'], ['Ghana', 'GH', '+233'], ['Kenya', 'KE', '+254'],
            ['South Africa', 'ZA', '+27'], ['India', 'IN', '+91'], ['Pakistan', 'PK', '+92'],
            ['Bangladesh', 'BD', '+880'], ['Philippines', 'PH', '+63'], ['Indonesia', 'ID', '+62'],
            ['Egypt', 'EG', '+20'], ['Germany', 'DE', '+49'], ['France', 'FR', '+33'],
            ['Spain', 'ES', '+34'], ['Italy', 'IT', '+39'], ['Brazil', 'BR', '+55'],
            ['Mexico', 'MX', '+52'], ['Australia', 'AU', '+61'], ['United Arab Emirates', 'AE', '+971'],
            ['Saudi Arabia', 'SA', '+966'], ['Turkey', 'TR', '+90'], ['Morocco', 'MA', '+212'],
            ['Cameroon', 'CM', '+237'], ['Uganda', 'UG', '+256'], ['Tanzania', 'TZ', '+255'],
            ['Rwanda', 'RW', '+250'], ['Nepal', 'NP', '+977'], ['Sri Lanka', 'LK', '+94'],
            ['Vietnam', 'VN', '+84'], ['Thailand', 'TH', '+66'], ['Malaysia', 'MY', '+60'],
            ['Singapore', 'SG', '+65'], ['Russia', 'RU', '+7'], ['Ukraine', 'UA', '+380'],
            ['Poland', 'PL', '+48'], ['Netherlands', 'NL', '+31'], ['Sweden', 'SE', '+46'],
            ['Norway', 'NO', '+47'], ['Denmark', 'DK', '+45'], ['Switzerland', 'CH', '+41'],
            ['Austria', 'AT', '+43'], ['Belgium', 'BE', '+32'], ['Ireland', 'IE', '+353'],
            ['Portugal', 'PT', '+351'], ['Greece', 'GR', '+30'], ['Czech Republic', 'CZ', '+420'],
            ['Romania', 'RO', '+40'], ['Hungary', 'HU', '+36'], ['Argentina', 'AR', '+54'],
            ['Chile', 'CL', '+56'], ['Colombia', 'CO', '+57'], ['Peru', 'PE', '+51'],
            ['Venezuela', 'VE', '+58'], ['Ecuador', 'EC', '+593'], ['Dominican Republic', 'DO', '+1'],
            ['Jamaica', 'JM', '+1'], ['Trinidad and Tobago', 'TT', '+1'], ['Panama', 'PA', '+507'],
            ['Costa Rica', 'CR', '+506'], ['Ivory Coast', 'CI', '+225'], ['Senegal', 'SN', '+221'],
            ['Ethiopia', 'ET', '+251'], ['Sudan', 'SD', '+249'], ['Algeria', 'DZ', '+213'],
            ['Tunisia', 'TN', '+216'], ['Libya', 'LY', '+218'], ['Iraq', 'IQ', '+964'],
            ['Iran', 'IR', '+98'], ['Jordan', 'JO', '+962'], ['Lebanon', 'LB', '+961'],
            ['Kuwait', 'KW', '+965'], ['Qatar', 'QA', '+974'], ['Bahrain', 'BH', '+973'],
            ['Oman', 'OM', '+968'], ['Yemen', 'YE', '+967'], ['Afghanistan', 'AF', '+93'],
            ['China', 'CN', '+86'], ['Japan', 'JP', '+81'], ['South Korea', 'KR', '+82'],
            ['Taiwan', 'TW', '+886'], ['Hong Kong', 'HK', '+852'], ['Cambodia', 'KH', '+855'],
            ['Laos', 'LA', '+856'], ['Myanmar', 'MM', '+95'], ['Mongolia', 'MN', '+976'],
            ['Kazakhstan', 'KZ', '+7'], ['Uzbekistan', 'UZ', '+998'], ['Azerbaijan', 'AZ', '+994'],
            ['Armenia', 'AM', '+374'], ['Georgia', 'GE', '+995'], ['Belarus', 'BY', '+375'],
            ['Lithuania', 'LT', '+370'], ['Latvia', 'LV', '+371'], ['Estonia', 'EE', '+372'],
            ['Bulgaria', 'BG', '+359'], ['Croatia', 'HR', '+385'], ['Serbia', 'RS', '+381'],
            ['Slovakia', 'SK', '+421'], ['Slovenia', 'SI', '+386'], ['Finland', 'FI', '+358'],
            ['Iceland', 'IS', '+354'], ['Luxembourg', 'LU', '+352'], ['Malta', 'MT', '+356'],
            ['Cyprus', 'CY', '+357'], ['Albania', 'AL', '+355'], ['Bosnia and Herzegovina', 'BA', '+387'],
            ['Macedonia', 'MK', '+389'], ['Montenegro', 'ME', '+382'], ['Moldova', 'MD', '+373'],
            ['New Zealand', 'NZ', '+64'], ['Fiji', 'FJ', '+679'], ['Papua New Guinea', 'PG', '+675'],
            ['Zambia', 'ZM', '+260'], ['Zimbabwe', 'ZW', '+263'], ['Malawi', 'MW', '+265'],
            ['Mozambique', 'MZ', '+258'], ['Botswana', 'BW', '+267'], ['Namibia', 'NA', '+264'],
            ['Lesotho', 'LS', '+266'], ['Eswatini', 'SZ', '+268'], ['Madagascar', 'MG', '+261'],
            ['Mauritius', 'MU', '+230'], ['Seychelles', 'SC', '+248'], ['Congo', 'CG', '+242'],
            ['DR Congo', 'CD', '+243'], ['Gabon', 'GA', '+241'], ['Chad', 'TD', '+235'],
            ['Niger', 'NE', '+227'], ['Mali', 'ML', '+223'], ['Burkina Faso', 'BF', '+226'],
            ['Benin', 'BJ', '+229'], ['Togo', 'TG', '+228'], ['Guinea', 'GN', '+224'],
            ['Sierra Leone', 'SL', '+232'], ['Liberia', 'LR', '+231'], ['Mauritania', 'MR', '+222'],
            ['Cape Verde', 'CV', '+238'], ['Gambia', 'GM', '+220'], ['Central African Republic', 'CF', '+236'],
            ['Equatorial Guinea', 'GQ', '+240'], ['Djibouti', 'DJ', '+253'], ['Somalia', 'SO', '+252'],
            ['Eritrea', 'ER', '+291'], ['South Sudan', 'SS', '+211'], ['Comoros', 'KM', '+269'],
            ['Bhutan', 'BT', '+975'], ['Maldives', 'MV', '+960'], ['Brunei', 'BN', '+673'],
            ['Timor-Leste', 'TP', '+670'], ['Solomon Islands', 'SB', '+677'], ['Vanuatu', 'VU', '+678'],
            ['Samoa', 'WS', '+685'], ['Tonga', 'TO', '+676'], ['Kiribati', 'KI', '+686'],
            ['Tajikistan', 'TJ', '+992'], ['Kyrgyzstan', 'KG', '+996'], ['Turkmenistan', 'TM', '+993'],
            ['Syria', 'SY', '+963'], ['Palestine', 'PS', '+970'], ['Israel', 'IL', '+972'],
        ];

        $rows = [];
        foreach ($countries as $c) {
            $rows[] = ['name' => $c[0], 'code' => $c[1], 'phone_code' => $c[2]];
        }
        Country::upsert($rows, ['code'], ['name', 'phone_code']);
    }
}
