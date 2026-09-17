<?php
/**
 * admin_sidebar.php
 * All navigation links point to files within the same admin folder.
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 border-r border-slate-800">
    <div class="flex flex-col h-full">
        <!-- Logo Section -->
        <div class="flex items-center justify-between h-20 px-6 bg-slate-900 border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-violet-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="utensils" class="w-5 h-5 text-white"></i>
                </div>
                <span class="text-lg font-black tracking-tighter uppercase text-white">Smart<span class="text-violet-500">Canteen</span></span>
            </div>
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 mt-6 px-4 space-y-2 overflow-y-auto">
            <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Main Menu</p>
            
            <a href="dashboard.php" 
               class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'dashboard.php') ? 'text-white bg-violet-600 shadow-lg shadow-violet-900/20' : 'text-gray-400 hover:bg-slate-800 hover:text-white'; ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 <?php echo ($current_page == 'dashboard.php') ? 'text-white' : 'group-hover:text-violet-400'; ?>"></i>
                <span class="text-xs font-bold uppercase tracking-widest">Dashboard</span>
            </a>

            <a href="manage_items.php" 
               class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'manage_items.php' || $current_page == 'edit_item.php') ? 'text-white bg-violet-600 shadow-lg shadow-violet-900/20' : 'text-gray-400 hover:bg-slate-800 hover:text-white'; ?>">
                <i data-lucide="list" class="w-5 h-5 mr-3 <?php echo ($current_page == 'manage_items.php' || $current_page == 'edit_item.php') ? 'text-white' : 'group-hover:text-violet-400'; ?>"></i>
                <span class="text-xs font-bold uppercase tracking-widest">Manage Menu</span>
            </a>

            <!-- Clicking this opens add_item.php -->
            <a href="add_item.php" 
               class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'add_item.php') ? 'text-white bg-violet-600 shadow-lg shadow-violet-900/20' : 'text-gray-400 hover:bg-slate-800 hover:text-white'; ?>">
                <i data-lucide="plus-circle" class="w-5 h-5 mr-3 <?php echo ($current_page == 'add_item.php') ? 'text-white' : 'group-hover:text-violet-400'; ?>"></i>
                <span class="text-xs font-bold uppercase tracking-widest">Add New Item</span>
            </a>

            <a href="order_history.php" 
               class="flex items-center px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'order_history.php') ? 'text-white bg-violet-600 shadow-lg shadow-violet-900/20' : 'text-gray-400 hover:bg-slate-800 hover:text-white'; ?>">
                <i data-lucide="history" class="w-5 h-5 mr-3 <?php echo ($current_page == 'order_history.php') ? 'text-white' : 'group-hover:text-violet-400'; ?>"></i>
                <span class="text-xs font-bold uppercase tracking-widest">Order History</span>
            </a>
        </nav>

        <!-- Footer / User Info -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/50">
            <div class="flex items-center p-3 mb-4 bg-slate-800/40 rounded-2xl border border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white font-black text-sm uppercase">
                    <?php echo isset($_SESSION['user_name']) ? substr($_SESSION['user_name'], 0, 1) : 'A'; ?>
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-[10px] font-black text-white uppercase truncate"><?php echo $_SESSION['user_name'] ?? 'Admin User'; ?></p>
                    <div class="flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                        <p class="text-[9px] text-gray-500 font-bold uppercase tracking-tighter">Active Session</p>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="flex items-center px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all group">
                <i data-lucide="log-out" class="w-5 h-5 mr-3 group-hover:translate-x-1 transition-transform"></i>
                <span class="text-xs font-bold uppercase tracking-widest">Sign Out</span>
            </a>
        </div>
    </div>
</aside>

<script>
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) { sidebar.classList.toggle('-translate-x-full'); }
    }
</script>