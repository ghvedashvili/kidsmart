<?php

/*
 * KidSmart — დამატებითი ფუნქციები კითხვის ფორმულებისთვის.
 * QuestionTemplate::evalExpr()/generateNumeric() ფორმულას eval()-ით ითვლის და
 * მხოლოდ MATH_FNS სიაში არსებულ სახელებს უშვებს — ეს ფუნქციები იქ არის დამატებული.
 * (form.blade.php-ის JS-ში იგივე ფუნქციებია გადახედვისთვის.)
 *
 *   topsum(k, a, b, c, ...)  — k ყველაზე დიდი რიცხვის ჯამი   („3 ყველაზე ძვირი ნივთი“)
 *   botsum(k, a, b, c, ...)  — k ყველაზე პატარა რიცხვის ჯამი
 *   kmax(k, a, b, c, ...)    — k-ური ყველაზე დიდი              (kmax(2, ...) = მეორე უდიდესი)
 *   kmin(k, a, b, c, ...)    — k-ური ყველაზე პატარა
 *   median(a, b, c, ...)     — შუა რიცხვი დალაგების შემდეგ (ლუწ რაოდენობაზე — ორი შუას საშუალო, ქვემოთ დამრგვალებული)
 */

if (! function_exists('topsum')) {
    function topsum($k, ...$xs) { rsort($xs); return array_sum(array_slice($xs, 0, max(0, (int) $k))); }
}
if (! function_exists('botsum')) {
    function botsum($k, ...$xs) { sort($xs); return array_sum(array_slice($xs, 0, max(0, (int) $k))); }
}
if (! function_exists('kmax')) {
    function kmax($k, ...$xs) { rsort($xs); return $xs[max(0, (int) $k - 1)] ?? 0; }
}
if (! function_exists('kmin')) {
    function kmin($k, ...$xs) { sort($xs); return $xs[max(0, (int) $k - 1)] ?? 0; }
}
if (! function_exists('median')) {
    function median(...$xs) {
        if (! $xs) return 0;
        sort($xs);
        $n = count($xs);
        return $n % 2 ? $xs[intdiv($n, 2)] : intdiv($xs[$n / 2 - 1] + $xs[$n / 2], 2);
    }
}
