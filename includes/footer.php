<?php $user = currentUser(); ?>
<?php if ($user && in_array($user['role'], ['admin', 'student'], true)): ?>
            </main>
        </div>
    </div>
<?php else: ?>
    </main>
    <footer class="public-footer">
        <div class="container footer-flex">
            <div>
                <strong>LMS Nova</strong>
                <p>Plateforme simple de gestion de cours en PHP / PDO.</p>
            </div>
            <p>Conçu pour répondre au cahier des charges.</p>
        </div>
    </footer>
<?php endif; ?>
</body>
</html>
