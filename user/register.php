<?php
include "../config/db.php";

$error = "";
$success = "";

if(isset($_POST['register']))
{
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Note: In production, use password_hash()

    /* Check if email already exists */
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");

    if($check->num_rows > 0)
    {
        $error = "Email already registered!";
    }
    else
    {
        // Using 'user' as the role to stay consistent with your place_order.php logic
        $sql = "INSERT INTO users(name, email, password, role) VALUES('$name', '$email', '$password', 'user')";
        
        if($conn->query($sql)) {
            $success = "Account created! You can login now.";
        } else {
            $error = "Registration failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Smart Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { 
            font-family: 'Outfit', sans-serif; 
            background: #fcfaff;
            background-image: radial-gradient(circle at 0% 0%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                              radial-gradient(circle at 100% 100%, rgba(30, 0, 51, 0.05) 0%, transparent 50%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center bg-violet-600 p-3 rounded-3xl shadow-xl rotate-3 mb-4">
                <i data-lucide="zap" class="w-8 h-8 text-white fill-current"></i>
            </div>
            <h1 class="text-3xl font-black tracking-tighter uppercase text-[#1e0033]">
                Smart<span class="text-violet-500">Canteen</span>
            </h1>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-[0.2em] mt-2">Join the future of dining</p>
        </div>

        <div class="glass-card p-10 rounded-[3rem] shadow-2xl shadow-violet-100">
            <h2 class="text-xl font-black text-[#1e0033] mb-8 uppercase tracking-tight">Create Account</h2>

            <?php if($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center text-red-600 animate-pulse">
                    <i data-lucide="alert-circle" class="w-4 h-4 mr-3"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest"><?= $error ?></p>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-2xl flex items-center text-green-600">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-3"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest"><?= $success ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Full Name</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-300"></i>
                        <input type="text" name="name" required placeholder="John Doe" 
                               class="w-full bg-gray-50/50 px-12 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-bold text-gray-800 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-300"></i>
                        <input type="email" name="email" required placeholder="name@example.com" 
                               class="w-full bg-gray-50/50 px-12 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-bold text-gray-800 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-300"></i>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full bg-gray-50/50 px-12 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-bold text-gray-800 text-sm">
                    </div>
                </div>

                <button name="register" class="w-full bg-violet-600 hover:bg-[#1e0033] text-white py-5 rounded-2xl font-black uppercase text-[10px] tracking-[0.3em] transition-all shadow-xl shadow-violet-200 hover:shadow-none active:scale-95">
                    Sign Up Now
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                    Have an account? 
                    <a href="login.php" class="text-violet-600 hover:text-[#1e0033] transition-colors ml-1">Login here</a>
                </p>
            </div>
        </div>
        
        <p class="mt-8 text-center text-[9px] font-black text-gray-300 uppercase tracking-[0.5em]">Efficiency Driven Dining &copy; 2025</p>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>