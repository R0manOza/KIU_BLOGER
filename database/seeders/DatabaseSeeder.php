<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Event;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: skip if the demo data has already been seeded.
        // This makes it safe to run automatically on every deploy.
        if (User::where('email', 'admin@kiu.edu.ge')->exists()) {
            return;
        }

        // ---- Users -------------------------------------------------------
        $admin = User::create([
            'name' => 'KIU Admin',
            'email' => 'admin@kiu.edu.ge',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);
        $admin->profile()->create([
            'major' => 'Administration',
            'bio' => 'Managing the KIU Blogger platform.',
        ]);

        $authors = collect([
            ['Nino Beridze', 'nino@kiu.edu.ge', 'Computer Science'],
            ['Giorgi Kapanadze', 'giorgi@kiu.edu.ge', 'Business Management'],
            ['Mariam Lomidze', 'mariam@kiu.edu.ge', 'Mathematics'],
        ])->map(function ($data) {
            $user = User::create([
                'name' => $data[0],
                'email' => $data[1],
                'password' => Hash::make('password'),
            ]);
            $user->profile()->create([
                'major' => $data[2],
                'bio' => "Student at KIU studying {$data[2]}.",
            ]);

            return $user;
        });

        // ---- Categories --------------------------------------------------
        $categories = collect([
            'Campus Life', 'Academics', 'Technology', 'Events', 'Student Tips',
        ])->map(fn ($name) => Category::create(['name' => $name]));

        // ---- Tags --------------------------------------------------------
        $tags = collect([
            'kiu', 'study', 'programming', 'career', 'freshman', 'research', 'community',
        ])->map(fn ($name) => Tag::create(['name' => $name]));

        // ---- Posts -------------------------------------------------------
        $samplePosts = [
            ['Welcome to KIU Blogger', 'Campus Life', 'A place for every student to share their voice.'],
            ['5 Study Tips for Your First Semester', 'Student Tips', 'How to survive and thrive in your first months at KIU.'],
            ['Why I Chose Computer Science at KIU', 'Academics', 'My journey into the world of code and algorithms.'],
            ['The Best Spots to Study on Campus', 'Campus Life', 'Quiet corners and cozy cafes around the university.'],
            ['Upcoming Tech Meetups This Semester', 'Events', 'Networking opportunities you should not miss.'],
            ['Getting Started with Laravel', 'Technology', 'A beginner-friendly look at the PHP framework powering this blog.'],
        ];

        $allUsers = $authors->concat([$admin]);

        foreach ($samplePosts as $i => [$title, $catName, $excerpt]) {
            $author = $authors->random();
            $post = Post::create([
                'user_id' => $author->id,
                'category_id' => $categories->firstWhere('name', $catName)?->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => $excerpt,
                'body' => $this->sampleBody($title),
                'is_published' => true,
                'published_at' => now()->subDays(count($samplePosts) - $i),
            ]);

            $post->tags()->attach($tags->random(rand(2, 3))->pluck('id'));

            // A couple of comments per post.
            foreach (range(1, rand(1, 3)) as $c) {
                Comment::create([
                    'user_id' => $authors->random()->id,
                    'post_id' => $post->id,
                    'body' => collect([
                        'Great post, thanks for sharing!',
                        'This was really helpful.',
                        'I totally agree with this.',
                        'Looking forward to more content like this.',
                    ])->random(),
                ]);
            }

            // Random up/down votes (mostly upvotes) from other users.
            $allUsers->where('id', '!=', $author->id)
                ->random(rand(1, $allUsers->count() - 1))
                ->each(fn ($voter) => $post->votes()->create([
                    'user_id' => $voter->id,
                    'value' => rand(1, 5) === 1 ? -1 : 1,
                ]));
        }

        // ---- Follows -----------------------------------------------------
        // Each user follows a couple of random other users; some also follow
        // their events (auto-add to calendar).
        $allUsers->each(function ($user) use ($allUsers) {
            $targets = $allUsers->where('id', '!=', $user->id)->random(rand(1, 2));
            foreach ($targets as $target) {
                $user->following()->syncWithoutDetaching([
                    $target->id => ['follow_events' => (bool) rand(0, 1)],
                ]);
            }
        });

        // ---- Events ------------------------------------------------------
        $eventSeeds = [
            ['Welcome to KIU Blogger', 'Orientation Mixer', 'Main Hall', 3],
            ['Upcoming Tech Meetups This Semester', 'KIU Dev Meetup #1', 'Lab B204', 7],
            ['Getting Started with Laravel', 'Laravel Workshop', 'Room 110', 10],
        ];

        foreach ($eventSeeds as [$postTitle, $eventTitle, $location, $inDays]) {
            $post = Post::where('title', $postTitle)->first();
            $creator = $post?->user ?? $authors->random();

            $event = Event::create([
                'user_id' => $creator->id,
                'post_id' => $post?->id,
                'title' => $eventTitle,
                'description' => 'Join us for ' . $eventTitle . ' — open to all KIU students.',
                'location' => $location,
                'starts_at' => now()->addDays($inDays)->setTime(18, 0),
                'ends_at' => now()->addDays($inDays)->setTime(20, 0),
            ]);

            // A few other users add it to their calendar.
            $event->subscribers()->syncWithoutDetaching(
                $allUsers->where('id', '!=', $creator->id)->random(rand(1, 2))->pluck('id')->all()
            );
        }
    }

    private function sampleBody(string $title): string
    {
        return "This is a sample article titled \"{$title}\".\n\n"
            . "KIU Blogger is a student-driven publication built with the Laravel framework. "
            . "It demonstrates a full content management workflow: creating, reading, updating and deleting posts, "
            . "organising them into categories, tagging them, and letting the community join the conversation through comments.\n\n"
            . "Feel free to register an account, write your own posts, upload a cover image, and personalise your profile. "
            . "Every feature you see here is powered by Eloquent models, Blade templates and Laravel's routing and validation layers.";
    }
}
