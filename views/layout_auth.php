<?php
$appName = $config['app']['name'] ?? 'SMM Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=h($appName)?> · Account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: { DEFAULT: "#0A3B95", light: "#1E57C3" },
            secondary: "#D4AF37",
            background: { light: "#F0F2F5", dark: "#080C14" },
          },
          fontFamily: { display: ["Inter","sans-serif"] },
          borderRadius: { DEFAULT: "0.75rem", lg: "1rem", xl: "1.5rem", full: "9999px" },
        },
      },
    };
  </script>
  <style>
    .bg-glass { backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,0.12); }
  </style>
</head>
<body class="min-h-screen bg-background-light dark:bg-background-dark text-gray-800 dark:text-gray-200">
  <div class="relative min-h-screen">
    <div class="absolute inset-0 -z-10 pointer-events-none" style="
      background:
        radial-gradient(900px 500px at 10% 8%, rgba(10,59,149,.24), transparent 60%),
        radial-gradient(700px 420px at 85% 20%, rgba(212,175,55,.18), transparent 60%),
        radial-gradient(800px 520px at 20% 85%, rgba(30,87,195,.12), transparent 60%);
      background-size: 200% 200%;
      animation: authshimmer 28s linear infinite;
    "></div>
    <header class="px-4 sm:px-8 py-4">
      <div class="mx-auto max-w-6xl flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-secondary/80 to-primary/70 text-white grid place-items-center">
            <span class="material-symbols-outlined">rocket</span>
          </div>
          <span class="text-lg sm:text-xl font-extrabold tracking-tight"><?=h($appName)?></span>
        </a>
        <a href="index.php?route=login" class="text-sm font-semibold hover:text-secondary transition">Login</a>
      </div>
    </header>
    <main class="px-4 sm:px-8 py-10">
      <div class="mx-auto max-w-3xl">
        <?= $content ?>
      </div>
    </main>
    <footer class="px-4 sm:px-8 py-8">
      <div class="mx-auto max-w-6xl text-center text-sm text-gray-600 dark:text-gray-400">
        &copy; <?=date('Y')?> <?=h($appName)?>. All rights reserved.
      </div>
    </footer>
  </div>
  <script>
    // motion-pref reduced
    (function(){
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('[class*="animate"]').forEach(el => el.style.animation = 'none');
      }
    })();
  </script>
</body>
</html>