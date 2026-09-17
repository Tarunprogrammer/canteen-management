<?php
session_start();
include "../config/db.php";

/**
 * 1. SECURITY CHECK
 * Ensure the user is actually logged in.
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

/**
 * 2. FETCH USER DETAILS
 * Removed 'created_at' as it does not exist in the users table schema.
 */
$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Formatting data
$initials = strtoupper(substr($user['name'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Smart Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { 
            font-family: 'Outfit', sans-serif; 
            background: #fcfaff;
            background-image: radial-gradient(circle at 100% 0%, rgba(139, 92, 246, 0.1) 0%, transparent 40%),
                              radial-gradient(circle at 0% 100%, rgba(30, 0, 51, 0.05) 0%, transparent 40%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-lg">
        
        <!-- Header / Navigation Back -->
        <div class="flex items-center justify-between mb-8 px-2">
            <a href="../index.php" class="group flex items-center space-x-2 text-gray-400 hover:text-violet-600 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Back to Menu</span>
            </a>
            <div class="bg-violet-600 p-2 rounded-xl shadow-lg shadow-violet-200">
                <i data-lucide="zap" class="w-4 h-4 text-white fill-current"></i>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="glass-card rounded-[3.5rem] overflow-hidden shadow-2xl shadow-violet-100">
            
            <!-- Cover / Avatar Area -->
            <div class="h-32 bg-gradient-to-r from-violet-600 to-indigo-700 relative">
                <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                    <div class="w-24 h-24 bg-white rounded-[2rem] shadow-xl flex items-center justify-center border-4 border-white">
                        <span class="text-4xl font-black text-violet-600"><?= $initials ?></span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="pt-16 pb-10 px-8 text-center">
                <h1 class="text-2xl font-black text-[#1e0033] tracking-tight uppercase"><?= htmlspecialchars($user['name']) ?></h1>
                <p class="text-[10px] font-black text-violet-500 uppercase tracking-[0.3em] mt-1 mb-8"><?= htmlspecialchars($user['role']) ?> Account</p>

                <div class="space-y-4 text-left">
                    <!-- Email Info -->
                    <div class="flex items-center p-5 bg-white/50 rounded-3xl border border-violet-50 group hover:border-violet-200 transition-all">
                        <div class="w-10 h-10 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center mr-4">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Email Address</p>
                            <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>

                    <!-- User ID Info -->
                    <div class="flex items-center p-5 bg-white/50 rounded-3xl border border-violet-50 group hover:border-violet-200 transition-all">
                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center mr-4">
                            <i data-lucide="fingerprint" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">System identifier</p>
                            <p class="text-sm font-bold text-gray-800">USR-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-10 grid grid-cols-1 gap-3">
                    <a href="logout.php" class="flex items-center justify-center space-x-2 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white py-5 rounded-[2rem] font-black uppercase text-[10px] tracking-[0.3em] transition-all active:scale-95 group">
                        <i data-lucide="log-out" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        <span>Secure Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <p class="mt-8 text-center text-[9px] font-black text-gray-300 uppercase tracking-[0.5em]">Efficiency Driven Dining &copy; 2025</p>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>