<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NicknameController extends Controller
{
private function getRules(string $nickname): array
{
    $nicknameUpper = strtoupper($nickname);
    $numbers = [];
    preg_match_all('/\d/', $nickname, $numbers);

    // საქართველოს დრო
    $now = new \DateTime('now', new \DateTimeZone('Asia/Tbilisi'));

    // დღე და თვე
    $dayKey = ["SUN","MON","TUE","WED","THU","FRI","SAT"][$now->format('w')];
    $monthIndex = (int)$now->format('n') - 1;

    $days = [
        "SUN" => ["SUNDAY", "კვირა", "კვი"],
        "MON" => ["MONDAY", "ორშაბათი", "ორშ"],
        "TUE" => ["TUESDAY", "სამშაბათი", "სამ"],
        "WED" => ["WEDNESDAY", "ოთხშაბათი", "ოთხ"],
        "THU" => ["THURSDAY", "ხუთშაბათი", "ხუთ"],
        "FRI" => ["FRIDAY", "პარასკევი", "პარ"],
        "SAT" => ["SATURDAY", "შაბათი", "შაბ"]
    ];
    $todayNames = array_merge([$dayKey], $days[$dayKey]);

    $months = [
        ["january","jan","იანვარი","იან"],
        ["february","feb","თებერვალი","თებ"],
        ["march","mar","მარტი","მარ"],
        ["april","apr","აპრილი","აპრ"],
        ["may","may","მაისი","მაი"],
        ["june","jun","ივნისი","ივნ"],
        ["july","jul","ივლისი","ივლ"],
        ["august","aug","აგვისტო","აგვ"],
        ["september","sep","სექტემბერი","სექ"],
        ["october","oct","ოქტომბერი","ოქტ"],
        ["november","nov","ნოემბერი","ნოე"],
        ["december","dec","დეკემბერი","დეკ"]
    ];
    $currentMonthNames = $months[$monthIndex];

    $compounds = ["H2O","NaCl"];
    $countryCodes = ["AR","AZ","GE","RU"];
    $primes = ["11","13","17","19","23","29","31","37","41","43","47","53","59","61","67","71","73","79","83","89","97"];

    return [
         ['id'=>9,'text'=>'Nickname უნდა შეიცავდეს ემოჯის','passed'=>preg_match('/[\x{1F300}-\x{1F9FF}]/u',$nickname)],
        ['id'=>16,'text'=>'Nickname არ უნდა შეიცავდეს ქართულ ასოებს','passed'=>!preg_match('/[\x{10D0}-\x{10FF}]/u',$nickname)],
         ['id'=>1,'text'=>'Nickname უნდა შეიცავდეს მინიმუმ 5 სიმბოლოს','passed'=>mb_strlen($nickname)>=5],
        ['id'=>2,'text'=>'Nickname უნდა შეიცავდეს ციფრს','passed'=>preg_match('/\d/',$nickname)],
        ['id'=>3,'text'=>'Nickname უნდა შეიცავდეს დიდ ასოს','passed'=>preg_match('/[A-Z]/',$nickname)],
        ['id'=>4,'text'=>'Nickname უნდა შეიცავდეს სპეციალურ სიმბოლოს','passed'=>preg_match('/[!@#$%^&*()_\-+=\[\]{};:"\\|,.<>\/?]/',$nickname)],
        ['id'=>5,'text'=>'Nickname-ში ციფრების ჯამი უნდა იყოს 15','passed'=>array_sum(array_map('intval',$numbers[0]??[]))===15],
        ['id'=>6,'text'=>'Nickname უნდა შეიცავდეს მიმდინარე კვირის დღეს','passed'=>collect($todayNames)->contains(fn($day)=>str_contains($nicknameUpper,strtoupper($day)))],
        ['id'=>7,'text'=>'Nickname უნდა შეიცავდეს ჭადრაკის ნოტაციას','passed'=>preg_match('/([a-h][1-8]|[nbrqk][a-h][1-8])/i',$nickname)],
        ['id'=>8,'text'=>'Nickname უნდა შეიცავდეს მიმდინარე თვეს','passed'=>collect($currentMonthNames)->contains(fn($m)=>str_contains(strtolower($nickname),strtolower($m)))],
        
        ['id'=>10,'text'=>'Nickname უნდა შეიცავდეს საქართველოს ან მისი მეზობელი ქვეყნის ISO კოდს','passed'=>collect($countryCodes)->contains(fn($c)=>str_contains($nicknameUpper,$c))],
        ['id'=>11,'text'=>'Nickname უნდა შეიცავდეს რომაულ ციფრს (მინიმუმ 3 სიმბოლო)','passed'=>preg_match('/[IVXLCDM]{3,}/i',$nickname)],
         ['id'=>12,'text'=>'Nickname-ის სიგრძე უნდა იყოს მაქსიმუმ 35 სიმბოლო','passed'=>mb_strlen($nickname)<=35],
        ['id'=>13,'text'=>'ყოველი მე-4 ასო უნდა იყოს დიდი','passed'=>function() use($nickname){
            $letters = preg_replace('/[^a-zA-Z]/','',$nickname);
            if(strlen($letters)<4) return false;
            for($i=3;$i<strlen($letters);$i+=4){
                if($letters[$i]!==strtoupper($letters[$i])) return false;
            }
            return true;
        }],
        ['id'=>14,'text'=>'Nickname უნდა შეიცავდეს 3 ხმოვანს ზედიზედ','passed'=>preg_match('/[aeiouAEIOU]{3,}/',$nickname)],
        ['id'=>15,'text'=>'Nickname უნდა შეიცავდეს ათწილადს','passed'=>preg_match('/\d+\.\d+/',$nickname)],
        
        ['id'=>17,'text'=>'Nickname არ უნდა შეიცავდეს მიმდევრობით ერთსა და იმავე სიმბოლოს 2-ზე მეტჯერ','passed'=>!preg_match('/(.)\1\1/',$nickname)],
       [
    'id' => 18,
    'text' => 'Nickname უნდა შეიცავდეს ტემპერატურას (-375°C-დან 10000°C-მდე ან °F)',
    'passed' => preg_match('/(-?\d{1,5})\s*(?:°|º|deg)\s*([CF])/iu', $nickname, $matches)
        && (
            (strtoupper($matches[2]) === 'C' && $matches[1] >= -375 && $matches[1] <= 10000) ||
            (strtoupper($matches[2]) === 'F')
        )
],
        ['id'=>19,'text'=>'Nickname უნდა შეიცავდეს 💧-ს ან 🧂-ს ქიმიური ნაერთის კოდს','passed'=>collect($compounds)->contains(fn($c)=>str_contains(strtoupper($nickname),strtoupper($c)))],
        ['id'=>20,'text'=>'Nickname უნდა შეიცავდეს საქართველოს ავტომობილების სტანდარტულ სარეგისტრაციო ნომერს','passed'=>preg_match('/[A-Z]{2}-\d{3}-[A-Z]{2}/',$nicknameUpper)],
        ['id'=>21,'text'=>'Nickname-ში არ უნდა იყოს "41"','passed'=>!str_contains($nickname,'41')],
        ['id'=>22,'text'=>'Nickname უნდა შეიცავდეს მარტივ 2 ნიშნა რიცხვს','passed'=>collect($primes)->contains(fn($p)=>str_contains($nickname,$p))],
        ['id'=>23,'text'=>'Nickname უნდა შეიცავდეს ციფრულ დროს (12:34, 23:59)','passed'=>preg_match('/([01]?[0-9]|2[0-3]):[0-5][0-9]/',$nickname)],
        ['id'=>24,'text'=>'Nickname-ში აკრძალულია კოდი RU (რუსეთი ოკუპანტია)','passed'=>!str_contains($nicknameUpper,'RU')],
        ['id'=>25,'text'=>'Nickname კენტი და ლუწი ციფრები უნდა იყოს თანაბარი რაოდენობის','passed'=>function() use($nickname){
            $numbers = preg_match_all('/\d/',$nickname,$matches) ? $matches[0] : [];
            $even = count(array_filter($numbers, fn($n)=>intval($n)%2===0));
            $odd  = count(array_filter($numbers, fn($n)=>intval($n)%2!==0));
            return $even === $odd;
        }],
        ['id'=>26,'text'=>'Nickname-ში ციფრების ნამრავლი 0-ზე მეტი უნდა იყოს','passed'=>function() use($nickname){
            $numbers = preg_match_all('/\d/',$nickname,$matches) ? $matches[0] : [];
            if(empty($numbers)) return false;
            $product = array_product(array_map('intval',$numbers));
            return $product > 0;
        }],
        ['id'=>999,'text'=>'აკრიფე ეს ქაფთჩა: "AIIM1"','passed'=>str_contains($nickname,'AIIM1')]
    ];
}




    private function normalizeRules(array $rules): array
    {
        return array_map(fn($r)=>[
            'id'=>$r['id'],
            'text'=>$r['text'],
            'passed'=>is_callable($r['passed'])?(bool)$r['passed'](): (bool)$r['passed']
        ], $rules);
    }

    public function live(Request $request, $level)
    {
        if (!$request->expectsJson()) return response()->json(['locked'=>true],400);

        $user=Auth::user();
        if(!$user || $user->level!=$level) return response()->json(['locked'=>true]);

        return response()->json([
            'locked'=>false,
            'rules'=>$this->normalizeRules($this->getRules($request->nickname??''))
        ]);
    }

    public function submit(Request $request,$level)
    {
        if(!$request->expectsJson()) return response()->json(['status'=>'error'],400);

        $user=Auth::user();
        if(!$user || $user->level!=$level) return response()->json(['status'=>'locked']);

        $nickname=$request->nickname??'';
        $rules=$this->normalizeRules($this->getRules($nickname));

        if(!collect($rules)->every(fn($r)=>$r['passed'])){
            return response()->json(['status'=>'error','rules'=>$rules]);
        }

        try{
            $user->nickname=$nickname;
            $user->level+=1;
            $user->save();
        }catch(\Throwable $e){
            return response()->json(['status'=>'error'],500);
        }

        return response()->json(['status'=>'success','nickname'=>$nickname,'newLevel'=>$user->level]);
    }
}
