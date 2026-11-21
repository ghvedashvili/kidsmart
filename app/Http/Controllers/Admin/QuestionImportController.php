<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionTemplate;
use App\Services\TemplateDraftService;
use App\Services\TemplateImportService;
use Illuminate\Http\Request;

/**
 * კითხვების JSON იმპორტი: AI-სთვის ინსტრუქცია → JSON → შემოწმება → შენახვა.
 * ან „ტექსტიდან“: ჩასმული ამოცანები → TemplateDraftService (AI-ს გარეშე) → იგივე JSON → შემოწმება.
 * შემოწმებული კითხვები სესიაშია (ks_qimport), შენახვისას ხელახლა მოწმდება.
 */
class QuestionImportController extends Controller
{
    public function index(TemplateImportService $svc)
    {
        return view('admin.questions.import', [
            'prompt'  => $svc->buildPrompt(),
            'json'    => session('ks_qimport.json', ''),
            'text'    => session('ks_qimport.text', ''),
            'questions' => $this->sessionQuestions()[1],
            'result'  => session('ks_qimport.result'),
            'defaults'=> session('ks_qimport.defaults', []),
            'topics'  => \App\Models\Topic::with('grade')->orderBy('grade_id')->orderBy('sequence')->get(),
            'themes'  => \App\Models\Theme::orderBy('name')->get(),
        ]);
    }

    public function check(Request $request, TemplateImportService $svc)
    {
        $request->validate([
            'json' => 'nullable|string|max:500000',
            'file' => 'nullable|file|max:2048',
            'default_topic_id' => 'nullable|integer|exists:topics,id',
            'default_theme_id' => 'nullable|integer|exists:themes,id',
        ]);
        $json = (string) $request->input('json', '');
        if ($request->hasFile('file')) $json = (string) file_get_contents($request->file('file')->getRealPath());
        if (trim($json) === '') return back()->withErrors(['json' => 'ჩასვი JSON ან ატვირთე ფაილი.']);

        $defaults = ['topic_id' => $request->input('default_topic_id'), 'theme_id' => $request->input('default_theme_id')];
        $result = $svc->parse($json, $defaults);
        session(['ks_qimport.json' => $json, 'ks_qimport.result' => $result, 'ks_qimport.defaults' => $defaults]);

        return redirect()->route('admin.questions.import.index')->withFragment('results');
    }

    /** ტექსტიდან შაბლონები — უფასო ოსტატი, AI-ს გარეშე */
    public function draft(Request $request, TemplateDraftService $draft, TemplateImportService $svc)
    {
        $request->validate([
            'text'             => 'required|string|max:100000',
            'default_topic_id' => 'required|integer|exists:topics,id',
            'default_theme_id' => 'nullable|integer|exists:themes,id',
        ], [
            'text.required'             => 'ჩასვი ამოცანები.',
            'default_topic_id.required' => 'აირჩიე თემა — ოსტატს კლასისა და თემის გარეშე კითხვის შენახვა არ შეუძლია.',
        ]);
        $topicId = (int) $request->input('default_topic_id');
        $themeId = $request->filled('default_theme_id') ? (int) $request->input('default_theme_id') : null;

        $data = $draft->draft((string) $request->input('text'), $topicId, $themeId);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $defaults = ['topic_id' => $topicId, 'theme_id' => $themeId];
        $result = $data['questions']
            ? $svc->parse($json, $defaults)
            : ['items' => [], 'skipped' => $data['skipped'], 'fatal' => $data['skipped'] ? null : 'ამოცანები ვერ ამოვიცანი — თითოეული ცალკე აბზაცად ან „ამოცანა №1“ სათაურით ჩასვი.'];

        session([
            'ks_qimport.text' => (string) $request->input('text'),
            'ks_qimport.json' => $json,
            'ks_qimport.result' => $result,
            'ks_qimport.defaults' => $defaults,
        ]);
        return redirect()->route('admin.questions.import.index')->withFragment('results');
    }

    /** ერთი კითხვის ფორმულის ხელით შესწორება → პირობები/არასწორი პასუხები თავიდან → ხელახალი შემოწმება */
    public function formula(Request $request, TemplateDraftService $draft, TemplateImportService $svc)
    {
        $data = $request->validate([
            'index'   => 'required|integer|min:0',
            'formula' => 'required|string|max:300',
        ], ['formula.required' => 'ჩაწერე ფორმულა.']);
        $i = (int) $data['index'];
        $result = session('ks_qimport.result');
        [$decoded, $list] = $this->sessionQuestions();
        if (! $result || ! isset($list[$i]) || ! isset($result['items'][$i])) {
            return redirect()->route('admin.questions.import.index')->withErrors(['json' => 'შემოწმების შედეგი ვადაგასულია — თავიდან შეამოწმე.']);
        }
        if (($result['items'][$i]['status'] ?? null) === 'saved') {
            return redirect()->route('admin.questions.import.index')->withErrors(['json' => 'ეს კითხვა უკვე შენახულია — ფორმულა მის რედაქტირების გვერდზე შეცვალე.']);
        }

        $list[$i] = $draft->refit(is_array($list[$i]) ? $list[$i] : [], $data['formula']);
        if (is_array($decoded) && isset($decoded['questions'])) $decoded['questions'] = $list; else $decoded = $list;
        $json = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $result['items'][$i] = $svc->check($list[$i], $i, session('ks_qimport.defaults', []));
        session(['ks_qimport.json' => $json, 'ks_qimport.result' => $result]);

        return redirect()->route('admin.questions.import.index')->withFragment('qi-item-' . $i);
    }

    /** @return array{0: mixed, 1: array} სესიის JSON (გაშიფრული) და კითხვების სია */
    private function sessionQuestions(): array
    {
        $raw = trim((string) session('ks_qimport.json', ''));
        $raw = preg_replace('/^`{3}(?:json)?\s*|\s*`{3}$/u', '', $raw);
        $d = json_decode($raw, true);
        if (! is_array($d)) return [null, []];
        $list = isset($d['questions']) && is_array($d['questions']) ? array_values($d['questions']) : (array_is_list($d) ? $d : [$d]);
        return [$d, $list];
    }

    public function save(Request $request, TemplateImportService $svc)
    {
        $data = $request->validate([
            'pick'     => 'required|array|min:1',
            'pick.*'   => 'integer|min:0',
            'activate' => 'nullable|boolean',
            'edit'     => 'nullable|integer|min:0',
        ]);
        $result = session('ks_qimport.result');
        if (! $result || empty($result['items'])) {
            return redirect()->route('admin.questions.import.index')->withErrors(['json' => 'შემოწმების შედეგი ვადაგასულია — თავიდან შეამოწმე JSON.']);
        }

        $saved = [];
        $skipped = 0;
        $activate = $request->boolean('activate', true);
        foreach (array_unique($data['pick']) as $i) {
            $item = $result['items'][$i] ?? null;
            if (! $item || in_array($item['status'], ['error', 'saved'], true)) { $skipped++; continue; }
            $attrs = $item['attrs'];
            $attrs['is_active'] = $activate && ! isset($data['edit']);
            $saved[$i] = QuestionTemplate::create($attrs)->id;
        }

        // შენახულები სესიიდან ამოვიღოთ, რომ ორჯერ არ შეინახოს
        foreach (array_keys($saved) as $i) {
            $result['items'][$i]['status'] = 'saved';
            $result['items'][$i]['saved_id'] = $saved[$i];
        }
        session(['ks_qimport.result' => $result]);

        if (isset($data['edit']) && isset($saved[$data['edit']])) {
            return redirect()->route('admin.questions.edit', $saved[$data['edit']])
                ->with('success', 'კითხვა შეინახა (არააქტიური) — გადახედე და გაააქტიურე.');
        }

        $msg = count($saved) . ' კითხვა შეინახა' . ($skipped ? " · {$skipped} გამოტოვდა შეცდომის გამო" : '');
        return redirect()->route('admin.questions.import.index')->with('success', $msg)->withFragment('results');
    }

    public function clear()
    {
        session()->forget(['ks_qimport.json', 'ks_qimport.text', 'ks_qimport.result', 'ks_qimport.defaults']);
        return redirect()->route('admin.questions.import.index');
    }
}
