<?php $user = currentUser(); ?>
<?php if ($user && in_array($user['role'], ['admin', 'student'], true)): ?>
            </main>
        </div>
    </div>
<?php else: ?>
    </main>
    <footer class="border-t border-white/10 mt-16">
        <div class="max-w-[1180px] mx-auto px-6 py-8">
            <p class="text-sm text-slate-500 text-center">copyright &copy; 2026 LMS. Tous droits réservés.</p>
        </div>
    </footer>
<?php endif; ?>
</body>
</html>