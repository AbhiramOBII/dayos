<?php

namespace App\Livewire\Admin;

use App\Enums\TaskPoints;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\UpskillingGoal;
use App\Services\AnthropicService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Upskilling extends Component
{
    public bool $showModal  = false;
    public int  $step       = 1;

    public string $skill      = '';
    public string $targetDate = '';
    public string $context    = '';

    public ?string $roadmapSummary = null;
    public array   $generatedTasks = [];
    public ?string $aiError        = null;

    public function openModal(): void
    {
        $this->reset('skill', 'targetDate', 'context', 'roadmapSummary', 'generatedTasks', 'aiError');
        $this->step      = 1;
        $this->showModal = true;
    }

    public function submitForm(): void
    {
        $this->validate([
            'skill'      => 'required|string|min:3|max:200',
            'targetDate' => 'required|date|after:today',
        ]);

        $this->step = 2;
    }

    public function generate(): void
    {
        $this->aiError = null;

        try {
            $today    = now()->toDateString();
            $daysLeft = (int) now()->diffInDays($this->targetDate);

            $validPoints = implode(', ', array_column(TaskPoints::cases(), 'value'));
            $contextLine = $this->context ? "Additional context from user: {$this->context}" : '';

            $system = 'You are a learning coach. Respond ONLY with raw valid JSON. No markdown fences, no explanation.';

            $prompt = <<<EOT
The user wants to learn "{$this->skill}" by {$this->targetDate} ({$daysLeft} days from today, {$today}).
{$contextLine}

Build a practical learning roadmap as JSON tasks. Each task should take 1-3 hours.

Return ONLY this JSON structure:
{
  "roadmap_summary": "2-3 sentence overview of the path",
  "tasks": [
    {
      "title": "Specific task title (max 60 chars)",
      "description": "Exactly what to do (max 120 chars)",
      "value_points": <one of: {$validPoints}>,
      "tbcb_date": "YYYY-MM-DD"
    }
  ]
}

Rules:
- 5 to 10 tasks total
- First task tbcb_date MUST be {$today}
- Spread tbcb_dates evenly from {$today} to {$this->targetDate}
- value_points: 3=tiny, 5=quick, 8=easy, 13=moderate, 21=solid, 34=complex, 55=major milestone
- Tasks are sequential — each builds on the previous
EOT;

            $response = app(AnthropicService::class)->message($prompt, $system, 1800);

            $json = preg_replace('/^```json?\s*|\s*```$/m', '', trim($response));
            $data = json_decode($json, true);

            if (! isset($data['tasks']) || ! is_array($data['tasks'])) {
                throw new \Exception('Invalid AI response');
            }

            $this->roadmapSummary = $data['roadmap_summary'] ?? null;
            $this->generatedTasks = $data['tasks'];
            $this->step           = 3;

        } catch (\Exception) {
            $this->aiError = 'Could not generate roadmap. Please try again.';
            $this->step    = 1;
        }
    }

    public function removeTask(int $index): void
    {
        array_splice($this->generatedTasks, $index, 1);
        $this->generatedTasks = array_values($this->generatedTasks);
    }

    public function confirm(): void
    {
        $validPoints = array_column(TaskPoints::cases(), 'value');

        $goal = UpskillingGoal::create([
            'skill'       => $this->skill,
            'description' => $this->context ?: null,
            'target_date' => $this->targetDate,
            'status'      => 'active',
            'ai_roadmap'  => $this->roadmapSummary,
        ]);

        foreach ($this->generatedTasks as $t) {
            $pts = in_array((int) ($t['value_points'] ?? 5), $validPoints)
                ? (int) $t['value_points']
                : 5;

            Task::create([
                'title'              => $t['title'],
                'short_description'  => $t['description'] ?? null,
                'value_points'       => $pts,
                'status'             => TaskStatus::Backlog->value,
                'tbcb_date'          => $t['tbcb_date'] ?? null,
                'is_archived'        => false,
                'upskilling_goal_id' => $goal->id,
            ]);
        }

        $this->showModal = false;
        $this->reset('skill', 'targetDate', 'context', 'roadmapSummary', 'generatedTasks');
    }

    public function abandonGoal(int $goalId): void
    {
        UpskillingGoal::findOrFail($goalId)->update(['status' => 'abandoned']);
    }

    public function render()
    {
        $goals = UpskillingGoal::with(['tasks'])->orderByDesc('created_at')->get();

        return view('livewire.admin.upskilling', compact('goals'));
    }
}
