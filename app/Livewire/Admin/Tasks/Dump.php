<?php

namespace App\Livewire\Admin\Tasks;

use App\Models\Pillar;
use App\Models\Task;
use App\Services\AnthropicService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dump extends Component
{
    public string $step = 'dump';
    public string $rawDump = '';
    public array $generatedTasks = [];
    public string $errorMessage = '';

    public function process(): void
    {
        $this->validate(['rawDump' => 'required|string|min:3']);
        $this->errorMessage = '';

        try {
            $pillarSlugs = Pillar::orderBy('name')->pluck('slug')->join(', ');

            $systemPrompt = <<<PROMPT
You are a task management assistant for DayOS. Convert raw task descriptions into structured task tickets.

Available pillar slugs (use only these exact values): {$pillarSlugs}

Fibonacci story points (choose the most appropriate):
- 3: Trivial, very quick (< 1 hour)
- 5: Simple, well-understood (a few hours)
- 8: Moderate complexity (half a day)
- 13: Complex, some unknowns (1-2 days)
- 21: Very complex, significant effort (3-5 days)
- 34: Epic-level, needs breakdown but keeping as one (1-2 weeks)
- 55: Massive, extremely complex (2+ weeks)

Task statuses: backlog, wip, completed (default to backlog unless clearly in progress or done)

Return ONLY a raw JSON array. No markdown, no code fences, no commentary.
Each object must have exactly these keys:
- title: string (concise, action-oriented, max 100 chars)
- short_description: string or null (1-2 sentences elaborating the task)
- value_points: integer (one of: 3, 5, 8, 13, 21, 34, 55)
- status: string (one of: backlog, wip, completed)
- pillars: array of slug strings (only from the available list above, pick the most relevant)
PROMPT;

            $userMessage = "Convert each line below into a structured task ticket. Treat each non-empty line as a separate task.\n\nRaw tasks:\n{$this->rawDump}";

            $response = app(AnthropicService::class)->message($userMessage, $systemPrompt, 4096);

            $this->generatedTasks = $this->parseResponse($response);
            $this->step = 'review';
        } catch (\Exception $e) {
            $this->errorMessage = 'AI processing failed: ' . $e->getMessage();
        }
    }

    public function setTaskField(int $index, string $field, mixed $value): void
    {
        $this->generatedTasks[$index][$field] = $value;
    }

    public function removeTask(int $index): void
    {
        array_splice($this->generatedTasks, $index, 1);
        $this->generatedTasks = array_values($this->generatedTasks);
    }

    public function saveAll(): void
    {
        if (empty($this->generatedTasks)) {
            return;
        }

        $pillars = Pillar::all()->keyBy('slug');

        foreach ($this->generatedTasks as $taskData) {
            $task = Task::create([
                'title'             => $taskData['title'],
                'short_description' => $taskData['short_description'] ?: null,
                'value_points'      => $taskData['value_points'],
                'status'            => $taskData['status'],
                'tbcb_date'         => null,
                'is_archived'       => false,
            ]);

            $pillarIds = collect($taskData['pillars'] ?? [])
                ->map(fn ($slug) => $pillars->get($slug)?->id)
                ->filter()
                ->values()
                ->toArray();

            $task->pillars()->sync($pillarIds);
        }

        session()->flash('success', count($this->generatedTasks) . ' tasks created successfully.');
        $this->redirect(route('admin.tasks.index'));
    }

    public function startOver(): void
    {
        $this->step = 'dump';
        $this->rawDump = '';
        $this->generatedTasks = [];
        $this->errorMessage = '';
    }

    private function parseResponse(string $response): array
    {
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = preg_replace('/```\s*/i', '', $response);
        $response = trim($response);

        $data = json_decode($response, true);

        if (! is_array($data)) {
            throw new \RuntimeException('Could not parse AI response as JSON. Raw: ' . substr($response, 0, 200));
        }

        $validPoints = [3, 5, 8, 13, 21, 34, 55];
        $validStatuses = ['backlog', 'wip', 'completed'];

        return array_values(array_map(function ($task) use ($validPoints, $validStatuses) {
            return [
                'title'             => trim($task['title'] ?? 'Untitled Task'),
                'short_description' => trim($task['short_description'] ?? ''),
                'value_points'      => in_array((int) ($task['value_points'] ?? 5), $validPoints) ? (int) $task['value_points'] : 5,
                'status'            => in_array($task['status'] ?? '', $validStatuses) ? $task['status'] : 'backlog',
                'pillars'           => is_array($task['pillars'] ?? null) ? $task['pillars'] : [],
            ];
        }, $data));
    }

    public function render()
    {
        return view('livewire.admin.tasks.dump', [
            'availablePillars' => Pillar::orderBy('name')->get(),
            'fibonacciPoints'  => [3, 5, 8, 13, 21, 34, 55],
            'statuses'         => ['backlog' => 'Backlog', 'wip' => 'WIP', 'completed' => 'Completed'],
        ]);
    }
}
