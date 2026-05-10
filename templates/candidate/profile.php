<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="max-w-4xl mx-auto p-6">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'profile_saved'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6">
            Profile updated successfully.
        </div>
    <?php endif; ?>

    <a href="portal" class="text-sm text-gray-500 hover:text-black flex items-center gap-2 mb-6 transition">
        ← Back to Candidate Portal
    </a>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 bg-gray-50/50">
            <h1 class="text-3xl font-bold text-gray-900">My Candidate Profile</h1>
            <p class="text-gray-600 mt-2">Manage your bio, skills, and public profile details for recruiters.</p>
        </div>

        <div class="p-8">
            <form action="profile" method="POST" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                <div class="grid gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 text-gray-900">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 text-gray-900">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </div>

                    <div>
                        <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">Professional Bio</label>
                        <textarea id="bio" name="bio" rows="5" class="w-full rounded-3xl border border-gray-300 px-4 py-4 text-gray-900" placeholder="Tell recruiters about your experience, strengths, and career goals."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-3xl p-6">
                    <h2 class="text-lg font-semibold mb-4">Skill Inventory</h2>
                    <p class="text-sm text-gray-500 mb-4">Select the skills that describe your expertise.</p>

                    <div class="mb-4">
                        <label for="skillSearch" class="sr-only">Search skills</label>
                        <input id="skillSearch" type="text" placeholder="Search skills..." class="w-full rounded-3xl border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:ring-2 focus:ring-black outline-none">
                    </div>

                    <div id="skillGrid" class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4 max-h-72 overflow-y-auto pr-1">
                        <?php if (!empty($skills)): ?>
                            <?php foreach ($skills as $skill): ?>
                                <?php $checked = in_array($skill['id'], $selectedSkillIds); ?>
                                <label class="skill-item flex items-center gap-2 rounded-2xl border border-gray-200 px-3 py-2 text-sm bg-white hover:border-black cursor-pointer">
                                    <input type="checkbox" name="skills[]" value="<?php echo (int)$skill['id']; ?>" class="accent-blue-600" <?php echo $checked ? 'checked' : ''; ?>>
                                    <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full text-sm text-gray-500">No skills are available yet. Contact an admin to add skills to the system.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="portal" class="text-sm text-gray-500 hover:text-black">Cancel</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-black px-6 py-3 text-sm font-semibold text-white hover:bg-gray-800 transition">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
    </main>

    <script>
        const skillSearch = document.getElementById('skillSearch');
        const skillItems = Array.from(document.querySelectorAll('.skill-item'));

        skillSearch.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            skillItems.forEach(item => {
                const name = item.querySelector('.skill-name').textContent.toLowerCase();
                item.style.display = name.includes(query) ? 'flex' : 'none';
            });
        });
    </script>

<?php include __DIR__ . '/../partials/footer.php'; ?>