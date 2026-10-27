<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Enums\ReminderStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Comment;
use App\Models\DailyReport;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ReminderLog;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkLogEntry;
use App\Notifications\MentionedInComment;
use App\Notifications\TaskAssigned;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $settings = Setting::current();
            $settings->update([
                'mail_from_address' => $settings->mail_from_address ?: 'admin.it@borneoprima.com',
                'mail_from_name' => $settings->mail_from_name ?: 'IT Worklog',
            ]);

            $categories = $this->seedCategories();
            $users = $this->seedUsers();
            $projects = $this->seedProjects($users);
            $tasks = $this->seedTasks($projects, $users);
            $this->seedTaskComments($tasks, $users);
            $this->seedWorklogs($users, $categories);
            $this->seedReportComments($users);
            $this->seedAttachments($users);
            $this->seedReminderLogs($users);
        });

        $this->command?->info('Demo data IT Worklog berhasil diisi.');
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $categories = [
            ['name' => 'Development', 'color' => '#0066FF', 'sort_order' => 1],
            ['name' => 'Support', 'color' => '#0284C7', 'sort_order' => 2],
            ['name' => 'Meeting', 'color' => '#D97706', 'sort_order' => 3],
            ['name' => 'Maintenance', 'color' => '#16A34A', 'sort_order' => 4],
            ['name' => 'Umum', 'color' => '#64748B', 'sort_order' => 5],
            ['name' => 'Infrastructure', 'color' => '#7C3AED', 'sort_order' => 6],
            ['name' => 'Security', 'color' => '#DC2626', 'sort_order' => 7],
            ['name' => 'Documentation', 'color' => '#0D9488', 'sort_order' => 8],
            ['name' => 'Testing', 'color' => '#CA8A04', 'sort_order' => 9],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                $category + ['is_active' => true],
            );
        }

        return Category::query()
            ->whereIn('name', array_column($categories, 'name'))
            ->get()
            ->keyBy('name')
            ->all();
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $adminEmail = (string) env('ADMIN_EMAIL', 'admin@borneoprima.com');
        $admin = User::query()->where('email', $adminEmail)->first()
            ?? User::query()->where('role', UserRole::Admin->value)->orderBy('id')->first();

        if (! $admin) {
            $admin = $this->demoUser('Administrator IT', $adminEmail, 'IT Administrator', UserRole::Admin);
        } else {
            $this->verify($admin);
        }

        return [
            'admin' => $admin,
            'manager' => $this->demoUser('Budi Santoso', 'manager@borneoprima.com', 'IT Manager', UserRole::Manager),
            'andika' => $this->demoUser('Andika Kuswidyarto', 'andika.kuswidyarto@borneoprima.com', 'Anggota Tim IT', UserRole::Member),
            'rina' => $this->demoUser('Rina Wulandari', 'rina.wulandari@borneoprima.com', 'System Analyst', UserRole::Member),
            'fajar' => $this->demoUser('Fajar Nugroho', 'fajar.nugroho@borneoprima.com', 'Infrastructure Engineer', UserRole::Member),
            'siti' => $this->demoUser('Siti Rahmawati', 'siti.rahmawati@borneoprima.com', 'Network Engineer', UserRole::Member),
            'dimas' => $this->demoUser('Dimas Pratama', 'dimas.pratama@borneoprima.com', 'Application Support', UserRole::Member),
        ];
    }

    private function demoUser(string $name, string $email, string $position, UserRole $role): User
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'position' => $position,
                'is_active' => true,
            ],
        );

        $this->verify($user);

        return $user;
    }

    private function verify(User $user): void
    {
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }

    /**
     * @param  array<string, User>  $users
     * @return array<string, Project>
     */
    private function seedProjects(array $users): array
    {
        $projects = [];

        foreach ($this->projectDefinitions() as $definition) {
            $project = Project::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'status' => $definition['status'],
                    'owner_id' => $users['manager']->id,
                    'start_date' => $definition['start_date'],
                    'due_date' => $definition['due_date'],
                    'completed_at' => $definition['status'] === ProjectStatus::Completed ? now()->subDays(5) : null,
                ],
            );

            $memberships = [];

            foreach ($definition['members'] as $memberKey) {
                $memberships[$users[$memberKey]->id] = ['role_in_project' => 'member'];
            }

            $project->members()->syncWithoutDetaching($memberships);
            $projects[$project->code] = $project;
            $this->seedMilestones($project, $definition['milestones']);
        }

        return $projects;
    }

    /**
     * @param  array<int, array<string, mixed>>  $milestones
     */
    private function seedMilestones(Project $project, array $milestones): void
    {
        foreach ($milestones as $index => $milestone) {
            Milestone::query()->updateOrCreate(
                [
                    'project_id' => $project->id,
                    'title' => $milestone['title'],
                ],
                [
                    'description' => $milestone['description'],
                    'due_date' => $milestone['due_date'],
                    'status' => $milestone['status'],
                    'completed_at' => $milestone['status'] === MilestoneStatus::Completed ? $milestone['due_date'] : null,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    /**
     * @param  array<string, Project>  $projects
     * @param  array<string, User>  $users
     * @return array<string, Task>
     */
    private function seedTasks(array $projects, array $users): array
    {
        $tasks = [];

        foreach ($this->taskDefinitions() as $definition) {
            $project = $projects[$definition['project']];
            $assignee = $users[$definition['assignee']];
            $milestone = Milestone::query()
                ->where('project_id', $project->id)
                ->where('title', $definition['milestone'])
                ->first();

            $status = $definition['status'];

            $task = Task::query()->updateOrCreate(
                [
                    'project_id' => $project->id,
                    'title' => $definition['title'],
                ],
                [
                    'milestone_id' => $milestone?->id,
                    'assignee_id' => $assignee->id,
                    'created_by_id' => $users['manager']->id,
                    'description' => $definition['description'],
                    'status' => $status,
                    'priority' => $definition['priority'],
                    'due_date' => $definition['due_date'],
                    'started_at' => in_array($status, [TaskStatus::InProgress, TaskStatus::Blocked, TaskStatus::Done], true)
                        ? now()->subDays(5)
                        : null,
                    'completed_at' => $status === TaskStatus::Done ? now()->subDays(2) : null,
                ],
            );

            if ($task->wasRecentlyCreated && $assignee->id !== $users['manager']->id) {
                $assignee->notify(new TaskAssigned($task, $users['manager']));
            }

            $tasks[$definition['key']] = $task;
        }

        return $tasks;
    }

    /**
     * @param  array<string, Task>  $tasks
     * @param  array<string, User>  $users
     */
    private function seedTaskComments(array $tasks, array $users): void
    {
        $comments = [
            [
                'task' => 'erp_uat',
                'author' => 'manager',
                'body' => '@rina.wulandari mohon prioritaskan skenario UAT laporan stok minggu ini.',
            ],
            [
                'task' => 'erp_uat',
                'author' => 'rina',
                'body' => 'Skenario utama sudah lulus. Sisa satu kasus untuk mutasi antar gudang.',
            ],
            [
                'task' => 'cloud_db',
                'author' => 'manager',
                'body' => '@fajar.nugroho pastikan window cutover dan rollback sudah disepakati.',
            ],
            [
                'task' => 'net_survey',
                'author' => 'manager',
                'body' => 'Hasil survei dipakai untuk finalisasi pengadaan switch dan access point.',
            ],
            [
                'task' => 'helpdesk_sla',
                'author' => 'dimas',
                'body' => 'Aturan eskalasi masih menunggu konfirmasi dari Kepala IT.',
            ],
        ];

        foreach ($comments as $seed) {
            $comment = Comment::query()->firstOrCreate([
                'commentable_type' => Task::class,
                'commentable_id' => $tasks[$seed['task']]->id,
                'user_id' => $users[$seed['author']]->id,
                'body' => $seed['body'],
            ]);

            if ($comment->wasRecentlyCreated) {
                $this->notifyMentions(
                    $comment,
                    $users[$seed['author']],
                    route('tasks.show', $tasks[$seed['task']]),
                );
            }
        }
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Category>  $categories
     */
    private function seedWorklogs(array $users, array $categories): void
    {
        $dates = [];

        for ($offset = 0; $offset < 16 && count($dates) < 8; $offset++) {
            $date = today()->copy()->subDays($offset);

            if ($date->dayOfWeek === 0) {
                continue;
            }

            $dates[] = $date;
        }

        $todayUsers = ['andika', 'rina', 'fajar'];

        foreach (['andika', 'rina', 'fajar', 'siti', 'dimas'] as $memberKey) {
            $user = $users[$memberKey];
            $assignedTasks = $user->assignedTasks()->with('project')->orderBy('id')->get();

            if ($assignedTasks->isEmpty()) {
                $assignedTasks = Task::query()->with('project')->orderBy('id')->get();
            }

            foreach ($dates as $date) {
                if ($date->isToday() && ! in_array($memberKey, $todayUsers, true)) {
                    continue;
                }

                $report = DailyReport::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'report_date' => $date->toDateString(),
                    ],
                    [
                        'notes' => $date->day % 4 === 0 ? 'Menunggu akses lingkungan staging dari tim infrastruktur.' : null,
                        'first_submitted_at' => $date->isToday() ? now()->subMinutes(45) : $date->copy()->setTime(16, 30),
                        'last_edited_at' => $date->isToday() ? now()->subMinutes(15) : $date->copy()->setTime(17, 5),
                    ],
                );

                if ($report->entries()->exists()) {
                    continue;
                }

                $entryCount = 1 + (($user->id + $date->day) % 2);

                for ($index = 0; $index < $entryCount; $index++) {
                    $task = $assignedTasks[($user->id + $date->day + $index) % $assignedTasks->count()];
                    $category = $this->categoryForTask($task, $date, $index, $categories);

                    $report->entries()->create([
                        'project_id' => $task->project_id,
                        'task_id' => $task->id,
                        'category_id' => $category?->id,
                        'description' => $this->descriptionFor($task, $index, $date),
                        'sort_order' => $index,
                    ]);
                }
            }
        }
    }

    /**
     * @param  array<string, Category>  $categories
     */
    private function categoryForTask(Task $task, CarbonInterface $date, int $index, array $categories): ?Category
    {
        $byProject = [
            'PRJ-2026-001' => ['Development', 'Testing', 'Documentation'],
            'PRJ-2026-002' => ['Infrastructure', 'Security', 'Maintenance'],
            'PRJ-2026-003' => ['Infrastructure', 'Maintenance'],
            'PRJ-2026-004' => ['Support', 'Development', 'Documentation'],
        ];

        $names = $byProject[$task->project?->code] ?? ['Umum'];
        $name = $names[($date->day + $index) % count($names)];

        return $categories[$name] ?? $categories['Umum'] ?? null;
    }

    private function descriptionFor(Task $task, int $index, CarbonInterface $date): string
    {
        $templates = [
            'Melanjutkan %s dan merapikan catatan implementasi.',
            'Menyelesaikan bagian %s sesuai acceptance criteria.',
            'Koordinasi dengan tim terkait %s.',
            'Review konfigurasi dan dokumentasi %s.',
            'Pengujian %s dan pencatatan hasil.',
            'Menindaklanjuti temuan review pada %s.',
        ];

        $template = $templates[($task->id + $date->day + $index) % count($templates)];

        return sprintf($template, Str::lower($task->title));
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedReportComments(array $users): void
    {
        $report = DailyReport::query()
            ->where('user_id', $users['rina']->id)
            ->latest('report_date')
            ->first();

        if (! $report) {
            return;
        }

        $comment = Comment::query()->firstOrCreate([
            'commentable_type' => DailyReport::class,
            'commentable_id' => $report->id,
            'user_id' => $users['manager']->id,
            'body' => '@rina.wulandari terima kasih, lanjutkan dengan skenario regresi besok.',
        ]);

        if ($comment->wasRecentlyCreated) {
            $this->notifyMentions($comment, $users['manager'], route('worklogs.show', $report));
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedAttachments(array $users): void
    {
        $this->seedEntryAttachment(
            $users['andika'],
            'laporan-progress.pdf',
            'application/pdf',
            $this->demoPdf(),
        );

        $this->seedEntryAttachment(
            $users['fajar'],
            'topologi-cloud.png',
            'image/png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        );
    }

    private function seedEntryAttachment(User $user, string $filename, string $mimeType, string $content): void
    {
        $entry = WorkLogEntry::query()
            ->whereHas('dailyReport', fn ($query) => $query->where('user_id', $user->id))
            ->latest('id')
            ->first();

        if (! $entry) {
            return;
        }

        $path = 'demo/worklogs/'.$entry->id.'/'.$filename;

        Storage::disk('local')->put($path, $content);

        Attachment::query()->updateOrCreate(
            ['path' => $path],
            [
                'attachable_type' => WorkLogEntry::class,
                'attachable_id' => $entry->id,
                'user_id' => $user->id,
                'disk' => 'local',
                'original_name' => $filename,
                'mime_type' => $mimeType,
                'size' => strlen($content),
            ],
        );
    }

    private function demoPdf(): string
    {
        $stream = 'BT /F1 18 Tf 72 720 Td (Laporan Demo IT Worklog - PT Borneo Prima) Tj ET';

        return "%PDF-1.4\n"
            ."1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n"
            ."2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n"
            ."3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n"
            ."4 0 obj\n<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream\nendobj\n"
            ."5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n"
            ."trailer\n<< /Root 1 0 R >>\n%%EOF";
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedReminderLogs(array $users): void
    {
        $members = ['andika', 'rina', 'fajar', 'siti', 'dimas'];

        foreach ($members as $index => $memberKey) {
            $date = today()->copy()->subDays(12 + $index);

            ReminderLog::query()->firstOrCreate(
                [
                    'user_id' => $users[$memberKey]->id,
                    'reminder_date' => $date->toDateString(),
                ],
                [
                    'status' => $index === 4 ? ReminderStatus::Failed : ReminderStatus::Sent,
                    'sent_at' => $index === 4 ? null : $date->copy()->setTime(17, 0),
                    'error' => $index === 4 ? 'SMTP timeout saat pengiriman demo.' : null,
                ],
            );
        }

        ReminderLog::query()->firstOrCreate(
            [
                'user_id' => $users['siti']->id,
                'reminder_date' => today()->copy()->subDays(18)->toDateString(),
            ],
            [
                'status' => ReminderStatus::Skipped,
                'sent_at' => null,
                'error' => 'Hari Minggu, pengingat dilewati.',
            ],
        );
    }

    private function notifyMentions(Comment $comment, User $author, string $url): void
    {
        preg_match_all('/(?<![\w.@])@([A-Za-z0-9._-]+)/', $comment->body, $matches);

        $handles = collect($matches[1] ?? [])
            ->map(fn (string $handle): string => Str::lower($handle))
            ->unique()
            ->values();

        if ($handles->isEmpty()) {
            return;
        }

        $users = User::query()
            ->where('is_active', true)
            ->get()
            ->filter(function (User $user) use ($handles): bool {
                $local = Str::before(Str::lower($user->email), '@');

                foreach ($handles as $handle) {
                    if ($local === $handle || str_starts_with($local, $handle.'.')) {
                        return true;
                    }
                }

                return false;
            })
            ->values();

        foreach ($users as $user) {
            if ($user->id !== $author->id) {
                $user->notify(new MentionedInComment($comment, $author, $url));
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function projectDefinitions(): array
    {
        return [
            [
                'code' => 'PRJ-2026-001',
                'name' => 'Implementasi ERP Modul Inventory',
                'description' => 'Implementasi modul inventory, integrasi supplier, dan UAT bersama tim gudang.',
                'status' => ProjectStatus::Active,
                'start_date' => today()->copy()->subDays(45),
                'due_date' => today()->copy()->addDays(30),
                'members' => ['andika', 'rina', 'fajar'],
                'milestones' => [
                    [
                        'title' => 'Blueprint dan setup awal',
                        'description' => 'Finalisasi kebutuhan, desain data, dan lingkungan pengembangan.',
                        'due_date' => today()->copy()->subDays(25),
                        'status' => MilestoneStatus::Completed,
                    ],
                    [
                        'title' => 'UAT dan go-live',
                        'description' => 'Uji terima pengguna, perbaikan temuan, dan peluncuran produksi.',
                        'due_date' => today()->copy()->addDays(20),
                        'status' => MilestoneStatus::InProgress,
                    ],
                ],
            ],
            [
                'code' => 'PRJ-2026-002',
                'name' => 'Migrasi Server ke Cloud',
                'description' => 'Migrasi aplikasi internal dan database ke lingkungan cloud dengan backup otomatis.',
                'status' => ProjectStatus::Active,
                'start_date' => today()->copy()->subDays(30),
                'due_date' => today()->copy()->addDays(14),
                'members' => ['fajar', 'dimas'],
                'milestones' => [
                    [
                        'title' => 'Audit dan persiapan',
                        'description' => 'Audit server lama, pemetaan dependensi, dan rencana cutover.',
                        'due_date' => today()->copy()->subDays(12),
                        'status' => MilestoneStatus::Completed,
                    ],
                    [
                        'title' => 'Cutover produksi',
                        'description' => 'Migrasi akhir, verifikasi, dan serah terima ke tim operasional.',
                        'due_date' => today()->copy()->addDays(10),
                        'status' => MilestoneStatus::InProgress,
                    ],
                ],
            ],
            [
                'code' => 'PRJ-2026-003',
                'name' => 'Perbaikan Jaringan Kantor Cabang',
                'description' => 'Peningkatan konektivitas, keamanan jaringan, dan dokumentasi topologi kantor cabang.',
                'status' => ProjectStatus::Active,
                'start_date' => today()->copy()->subDays(20),
                'due_date' => today()->copy()->addDays(40),
                'members' => ['siti', 'dimas'],
                'milestones' => [
                    [
                        'title' => 'Survei dan desain',
                        'description' => 'Survei lokasi, pengukuran sinyal, dan desain topologi.',
                        'due_date' => today()->copy()->addDays(5),
                        'status' => MilestoneStatus::InProgress,
                    ],
                    [
                        'title' => 'Implementasi perangkat',
                        'description' => 'Pemasangan switch, access point, dan konfigurasi VPN.',
                        'due_date' => today()->copy()->addDays(30),
                        'status' => MilestoneStatus::Pending,
                    ],
                ],
            ],
            [
                'code' => 'PRJ-2026-004',
                'name' => 'Aplikasi Helpdesk Internal',
                'description' => 'Aplikasi tiket internal untuk pencatatan insiden, permintaan layanan, dan SLA.',
                'status' => ProjectStatus::OnHold,
                'start_date' => today()->copy()->subDays(70),
                'due_date' => today()->copy()->addDays(60),
                'members' => ['rina', 'dimas'],
                'milestones' => [
                    [
                        'title' => 'Desain alur dan prototipe',
                        'description' => 'Wireframe, alur tiket, dan validasi bersama pengguna.',
                        'due_date' => today()->copy()->subDays(30),
                        'status' => MilestoneStatus::Completed,
                    ],
                    [
                        'title' => 'Pengembangan modul tiket',
                        'description' => 'Implementasi tiket, notifikasi, SLA, dan laporan.',
                        'due_date' => today()->copy()->addDays(25),
                        'status' => MilestoneStatus::Pending,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function taskDefinitions(): array
    {
        return [
            [
                'key' => 'erp_stock',
                'project' => 'PRJ-2026-001',
                'milestone' => 'UAT dan go-live',
                'assignee' => 'andika',
                'title' => 'Integrasi stok dengan portal supplier',
                'description' => 'Sinkronisasi stok, harga, dan status pengiriman dari portal supplier.',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::High,
                'due_date' => today()->copy()->addDays(5),
            ],
            [
                'key' => 'erp_uat',
                'project' => 'PRJ-2026-001',
                'milestone' => 'UAT dan go-live',
                'assignee' => 'rina',
                'title' => 'UAT modul laporan inventory',
                'description' => 'Uji laporan stok per gudang, mutasi, dan rekap periode.',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Urgent,
                'due_date' => today()->copy()->subDays(3),
            ],
            [
                'key' => 'erp_batch',
                'project' => 'PRJ-2026-001',
                'milestone' => 'UAT dan go-live',
                'assignee' => 'fajar',
                'title' => 'Perbaikan validasi nomor batch',
                'description' => 'Validasi duplikasi batch dan penanganan data lama.',
                'status' => TaskStatus::Blocked,
                'priority' => TaskPriority::High,
                'due_date' => today()->copy()->addDays(2),
            ],
            [
                'key' => 'erp_docs',
                'project' => 'PRJ-2026-001',
                'milestone' => 'Blueprint dan setup awal',
                'assignee' => 'andika',
                'title' => 'Dokumentasi API inventory',
                'description' => 'Dokumentasi endpoint, autentikasi, dan contoh payload.',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::Normal,
                'due_date' => today()->copy()->subDays(12),
            ],
            [
                'key' => 'erp_training',
                'project' => 'PRJ-2026-001',
                'milestone' => 'UAT dan go-live',
                'assignee' => 'rina',
                'title' => 'Training admin gudang',
                'description' => 'Sesi pelatihan dan penyusunan panduan singkat untuk admin gudang.',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Normal,
                'due_date' => today()->copy()->addDays(15),
            ],
            [
                'key' => 'cloud_audit',
                'project' => 'PRJ-2026-002',
                'milestone' => 'Audit dan persiapan',
                'assignee' => 'fajar',
                'title' => 'Audit keamanan server lama',
                'description' => 'Inventarisasi port, akses, dan konfigurasi keamanan server lama.',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::High,
                'due_date' => today()->copy()->subDays(10),
            ],
            [
                'key' => 'cloud_db',
                'project' => 'PRJ-2026-002',
                'milestone' => 'Cutover produksi',
                'assignee' => 'fajar',
                'title' => 'Migrasi database ke cloud',
                'description' => 'Replikasi database, verifikasi konsistensi, dan cutover.',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::Urgent,
                'due_date' => today()->copy()->addDays(3),
            ],
            [
                'key' => 'cloud_backup',
                'project' => 'PRJ-2026-002',
                'milestone' => 'Cutover produksi',
                'assignee' => 'dimas',
                'title' => 'Konfigurasi backup otomatis',
                'description' => 'Backup harian database dan storage dengan retensi 14 hari.',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::High,
                'due_date' => today()->copy()->addDays(7),
            ],
            [
                'key' => 'cloud_rollback',
                'project' => 'PRJ-2026-002',
                'milestone' => 'Audit dan persiapan',
                'assignee' => 'dimas',
                'title' => 'Dokumentasi prosedur rollback',
                'description' => 'Langkah rollback aplikasi dan database jika cutover gagal.',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::Normal,
                'due_date' => today()->copy()->subDays(8),
            ],
            [
                'key' => 'net_survey',
                'project' => 'PRJ-2026-003',
                'milestone' => 'Survei dan desain',
                'assignee' => 'siti',
                'title' => 'Survei jaringan kantor cabang',
                'description' => 'Pengukuran sinyal, pemetaan kabel, dan identifikasi titik rawan.',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::High,
                'due_date' => today()->copy()->subDays(2),
            ],
            [
                'key' => 'net_procure',
                'project' => 'PRJ-2026-003',
                'milestone' => 'Survei dan desain',
                'assignee' => 'dimas',
                'title' => 'Pengadaan switch dan access point',
                'description' => 'Finalisasi spesifikasi, permintaan penawaran, dan jadwal pengiriman.',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Normal,
                'due_date' => today()->copy()->addDays(10),
            ],
            [
                'key' => 'net_vpn',
                'project' => 'PRJ-2026-003',
                'milestone' => 'Implementasi perangkat',
                'assignee' => 'siti',
                'title' => 'Konfigurasi VPN site-to-site',
                'description' => 'Konfigurasi tunnel, routing, dan pengujian konektivitas.',
                'status' => TaskStatus::Blocked,
                'priority' => TaskPriority::High,
                'due_date' => today()->copy()->addDays(18),
            ],
            [
                'key' => 'net_doc',
                'project' => 'PRJ-2026-003',
                'milestone' => 'Survei dan desain',
                'assignee' => 'siti',
                'title' => 'Dokumentasi topologi jaringan',
                'description' => 'Diagram topologi dan daftar perangkat kantor cabang.',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::Low,
                'due_date' => today()->copy()->subDays(6),
            ],
            [
                'key' => 'helpdesk_design',
                'project' => 'PRJ-2026-004',
                'milestone' => 'Desain alur dan prototipe',
                'assignee' => 'rina',
                'title' => 'Desain alur tiket helpdesk',
                'description' => 'Alur tiket, status, prioritas, dan notifikasi dasar.',
                'status' => TaskStatus::Done,
                'priority' => TaskPriority::High,
                'due_date' => today()->copy()->subDays(32),
            ],
            [
                'key' => 'helpdesk_notify',
                'project' => 'PRJ-2026-004',
                'milestone' => 'Pengembangan modul tiket',
                'assignee' => 'dimas',
                'title' => 'Implementasi notifikasi email tiket',
                'description' => 'Notifikasi saat tiket dibuat, diubah, dan diselesaikan.',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Normal,
                'due_date' => today()->copy()->addDays(20),
            ],
            [
                'key' => 'helpdesk_sla',
                'project' => 'PRJ-2026-004',
                'milestone' => 'Pengembangan modul tiket',
                'assignee' => 'rina',
                'title' => 'Aturan SLA dan eskalasi',
                'description' => 'Definisi SLA per prioritas dan aturan eskalasi ke atasan.',
                'status' => TaskStatus::Blocked,
                'priority' => TaskPriority::Normal,
                'due_date' => today()->copy()->addDays(8),
            ],
        ];
    }
}
