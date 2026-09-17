<?php
session_start();
include "config/db.php";

/* ===============================
FETCH USER DETAILS FROM DB
================================*/
$user_logged_in = false;
$display_name = "";

if (isset($_SESSION['user_id'])) {
    $u_id = intval($_SESSION['user_id']);
    // Fetch directly from users table to ensure real-time name updates
    $user_query = "SELECT name FROM users WHERE id = $u_id LIMIT 1";
    $user_res = $conn->query($user_query);
    
    if ($user_res && $user_res->num_rows > 0) {
        $user_data = $user_res->fetch_assoc();
        $user_logged_in = true;
        $display_name = $user_data['name'];
    }
}

/* ===============================
FETCH MENU + IMAGES
================================*/
// Fetching menu items and joining with item_images
// Prioritizing item_images.image if it exists
$menu_query = "SELECT m.*, i.image AS gallery_img 
               FROM menu_items m 
               LEFT JOIN item_images i ON m.id = i.item_id 
               WHERE m.available = 1 
               ORDER BY m.id DESC";
$menu_result = $conn->query($menu_query);

/* ===============================
CART LOGIC
================================*/
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

if (isset($_POST['add_to_cart'])) {
    $item_id = intval($_POST['item_id']);
    $_SESSION['cart'][$item_id] = ($_SESSION['cart'][$item_id] ?? 0) + 1;
    // Use header to prevent form resubmission on refresh
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Canteen | Experience Quality</title>
    
    <!-- Modern Typography & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #ff4757;
            --secondary: #2f3542;
            --success: #2ed573;
            --bg: #f7faff;
            --white: #ffffff;
            --text-main: #1e272e;
            --text-muted: #808e9b;
            --radius-lg: 24px;
            --radius-md: 16px;
            --shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --glass: rgba(255, 255, 255, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* --- NAVIGATION --- */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 7%;
            background: var(--glass);
            backdrop-filter: blur(15px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        .logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--white);
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: var(--shadow);
            border: 1px solid #edf2f7;
            text-decoration: none;
            color: var(--text-main);
            font-weight: 600;
        }

        .user-pill .avatar {
            width: 32px;
            height: 32px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .cart-trigger {
            position: relative;
            background: var(--secondary);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--primary);
            color: white;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 7px;
            border-radius: 50px;
            border: 2px solid var(--white);
        }

        /* --- HERO SECTION --- */
        .hero-banner {
            margin: 30px 7%;
            height: 380px;
            background: linear-gradient(135deg, #FF9A8B 0%, #FF6A88 55%, #FF99AC 100%);
            border-radius: var(--radius-lg);
            position: relative;
            display: flex;
            align-items: center;
            padding: 0 60px;
            color: white;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(255, 71, 87, 0.2);
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .hero-content { position: relative; z-index: 2; max-width: 550px; }
        .hero-content h1 { font-size: 3.8rem; line-height: 1; margin-bottom: 15px; font-weight: 800; }
        .hero-content p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 25px; }

        /* --- CATEGORIES --- */
        .category-scroller {
            padding: 0 7% 40px;
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .category-scroller::-webkit-scrollbar { display: none; }

        .cat-item {
            min-width: 130px;
            background: var(--white);
            padding: 20px;
            border-radius: var(--radius-md);
            text-align: center;
            box-shadow: var(--shadow);
            cursor: pointer;
            border: 2px solid transparent;
        }
        .cat-item:hover { border-color: var(--primary); transform: translateY(-5px); }
        .cat-item i { font-size: 1.8rem; color: var(--primary); display: block; margin-bottom: 10px; }
        .cat-item span { font-weight: 600; font-size: 0.9rem; }

        /* --- MENU GRID --- */
        .container { padding: 0 7% 80px; }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 35px;
        }
        .section-header h2 { font-size: 2.2rem; font-weight: 800; letter-spacing: -0.5px; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 35px;
        }

        .food-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .food-card:hover { transform: translateY(-12px); box-shadow: 0 30px 60px -12px rgba(0,0,0,0.1); }

        .food-img {
            width: 100%;
            height: 220px;
            position: relative;
            overflow: hidden;
        }

        .food-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .food-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.95);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
        }

        .food-details { padding: 25px; }
        .food-details h3 { font-size: 1.4rem; margin-bottom: 8px; font-weight: 700; }
        .food-details .desc { color: var(--text-muted); font-size: 0.9rem; height: 42px; overflow: hidden; margin-bottom: 20px; }

        .food-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-tag { font-size: 1.7rem; font-weight: 800; color: var(--text-main); }
        .price-tag small { font-size: 1rem; color: var(--text-muted); font-weight: 400; }

        .add-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(255, 71, 87, 0.3);
        }

        .add-btn:hover { background: #ff6b81; transform: scale(1.05); }
        .add-btn:disabled { background: #d1d8e0; cursor: not-allowed; box-shadow: none; }

        .stock-info {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .hero-banner { height: auto; padding: 40px 30px; text-align: center; justify-content: center; }
            .hero-content h1 { font-size: 2.5rem; }
            nav { padding: 15px 5%; }
            .grid { gap: 20px; }
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">
        <i class="fas fa-utensils-alt"></i> SmartCanteen.
    </a>

    <div class="nav-right">
        <?php if($user_logged_in): ?>
            <div class="user-pill">
                <div class="avatar"><?= strtoupper(substr($display_name, 0, 1)) ?></div>
                <span><?= htmlspecialchars($display_name) ?></span>
            </div>

            <a href="user/cart.php" class="cart-trigger">
                <i class="fas fa-shopping-bag fa-lg"></i>
                <?php if(count($_SESSION['cart']) > 0): ?>
                    <span class="cart-badge"><?= array_sum($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>
            <a href="user/logout.php" style="color: var(--text-muted);"><i class="fas fa-sign-out-alt fa-lg"></i></a>
        <?php else: ?>
            <a href="user/login.php" class="user-pill" style="background: var(--primary); color: white; border: none;">Login</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-banner">
    <div class="hero-content">
        <h1>Freshness <br> in every bite.</h1>
        <p>Order from your canteen's finest selection and enjoy contactless delivery right at your seat.</p>
        <a href="#menu" style="background: white; color: var(--primary); padding: 14px 30px; border-radius: 50px; text-decoration: none; font-weight: 800;">Browse Menu</a>
    </div>
</section>

<!-- Categories -->
<div class="category-scroller">
    <div class="cat-item"><i class="fas fa-hamburger"></i><span>Burgers</span></div>
    <div class="cat-item"><i class="fas fa-pizza-slice"></i><span>Pizza</span></div>
    <div class="cat-item"><i class="fas fa-ice-cream"></i><span>Desserts</span></div>
    <div class="cat-item"><i class="fas fa-coffee"></i><span>Coffee</span></div>
    <div class="cat-item"><i class="fas fa-leaf"></i><span>Veggie</span></div>
    <div class="cat-item"><i class="fas fa-hotdog"></i><span>Snacks</span></div>
</div>

<div class="container" id="menu">
    <div class="section-header">
        <div>
            <h2>Popular Dishes</h2>
            <p style="color: var(--text-muted);">Curated selections for your taste buds.</p>
        </div>
        <div style="font-weight: 600; color: var(--primary); cursor: pointer;">View All <i class="fas fa-arrow-right"></i></div>
    </div>

    <div class="grid">
        <?php if ($menu_result && $menu_result->num_rows > 0): 
            while($item = $menu_result->fetch_assoc()):
                // Logic: item_images path, then menu_items path, then placeholder
                $img_name = $item['gallery_img'] ?: $item['image'];
                $img_path = "assets/images/" . $img_name;
                
                if (empty($img_name) || !file_exists($img_path)) {
                    $img_path = "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80";
                }
        ?>
        <div class="food-card">
            <div class="food-img">
                <img src="<?= $img_path ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <span class="food-badge"><?= htmlspecialchars($item['category'] ?: 'Special') ?></span>
            </div>
            
            <div class="food-details">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p class="desc"><?= htmlspecialchars($item['description'] ?: 'Fresh ingredients prepared with care by our top canteen chefs.') ?></p>
                
                <div class="food-footer">
                    <div class="price-tag"><small>₹</small><?= number_format($item['price'], 0) ?></div>
                    
                    <?php if($item['stock'] > 0): ?>
                        <form method="POST">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <button type="submit" name="add_to_cart" class="add-btn">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="add-btn" disabled>Sold Out</button>
                    <?php endif; ?>
                </div>
                
                <div class="stock-info">
                    <i class="fas <?= $item['stock'] > 10 ? 'fa-check-circle' : 'fa-exclamation-circle' ?>" 
                       style="color: <?= $item['stock'] > 10 ? '#2ed573' : '#ffa502' ?>"></i>
                    <?= $item['stock'] ?> units available
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                <i class="fas fa-box-open fa-4x" style="color: #d1d8e0; margin-bottom: 20px;"></i>
                <h3>No items found</h3>
                <p>Our chefs are preparing something special. Check back later!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>