<?php
/**
 * SHADOWS OF BANGLA — Admin Panel Footer Partial
 */
?>
    </main>

    <script>
        // Delete action confirmation helper
        function confirmDeletion(name, entityType = 'record') {
            return confirm(`Are you certain you wish to purge this ${entityType} [${name}] from the imperial archives? This action cannot be undone.`);
        }
    </script>
</body>
</html>

