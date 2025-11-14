<?php
session_start();

// Include the Database class
require_once 'Database.php';

// Instantiate the database connection
$database = new Database();
$db = $database->getConnection();

// Check if DB connection failed
if ($db === null) {
    die("<h1>Database Connection Failed!</h1><p>Check your .env and docker-compose.yml configuration.</p>");
}

// --- Configuration and Session Management ---
$is_admin = isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin';
$user_id = $_SESSION['user_id'] ?? 1; // Default to admin ID 1 if not logged in
$mode = isset($_GET['mode']) && $_GET['mode'] === 'admin' ? 'admin' : 'public';
$action = $_GET['action'] ?? 'list';
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


// --- CRUD OPERATIONS (Admin Only) ---
if ($is_admin) {
    // CREATE: Handle new post submission
    if ($action === 'create_post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
        $content = filter_input(INPUT_POST, 'content', FILTER_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO posts (title, content, author_id) VALUES (:title, :content, :author_id)");
        $stmt->execute([':title' => $title, ':content' => $content, ':author_id' => $user_id]);
        header('Location: index.php?mode=admin');
        exit;
    }

    // UPDATE: Handle existing post submission
    if ($action === 'update_post' && $_SERVER['REQUEST_METHOD'] === 'POST' && $post_id) {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
        $content = filter_input(INPUT_POST, 'content', FILTER_DEFAULT);
        
        $stmt = $db->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id");
        $stmt->execute([':title' => $title, ':content' => $content, ':id' => $post_id]);
        header('Location: index.php?mode=admin');
        exit;
    }

    // DELETE: Handle post deletion
    if ($action === 'delete' && $post_id) {
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute([':id' => $post_id]);
        header('Location: index.php?mode=admin');
        exit;
    }
}

// --- Logout Action ---
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// --- HTML HEADER ---
function render_header($title = "Blog Platform") {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <!-- Load Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
        /* Custom styles for the textarea/content editor */
        textarea { resize: vertical; min-height: 200px; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
<header class="bg-indigo-600 shadow-md">
    <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold text-white tracking-tight">
            The Dev Blog
        </a>
        <nav>
            <a href="index.php" class="text-white hover:text-indigo-200 transition duration-150 mr-4">Home</a>
            <a href="index.php?mode=admin" class="px-4 py-2 bg-white text-indigo-600 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition duration-150">
                Admin Panel
            </a>
        </nav>
    </div>
</header>
<main class="flex-grow max-w-4xl mx-auto w-full px-4 py-8">
HTML;
}

// --- HTML FOOTER ---
function render_footer() {
    echo <<<HTML
</main>
<footer class="bg-gray-800 text-white py-4 mt-8">
    <div class="max-w-4xl mx-auto px-4 text-center text-sm">
        &copy; 2024 The Dev Blog. All rights reserved.
    </div>
</footer>
</body>
</html>
HTML;
}

// --- VIEW RENDERING FUNCTIONS ---

function render_login_form($db, $login_error = null) {
    global $mode;

    // --- ADMIN LOGIN FORM & PROCESSING ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // This is where you might need the new hash if it was incorrect!
        if ($user && password_verify($password, $user['password_hash'])) { 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: index.php?mode=admin');
            exit;
        } else {
            $login_error = "Invalid credentials. Use 'admin' and 'securepassword123'.";
        }
    }
    
    // Logic to include the error message HTML if present
    $error_html = '';
    if ($login_error) {
        $error_html = "<p class='mt-4 text-center p-3 bg-red-100 text-red-700 border border-red-200 rounded-lg'>$login_error</p>";
    }

    render_header("Admin Login");
    echo <<<HTML
        <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-2xl border border-gray-100">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">🔐 Admin Login</h1>
            <p class="text-center text-sm text-gray-500 mb-6">Enter your credentials to access post management.</p>
            
            <form method="post" class="space-y-4">
                <input type="text" name="username" placeholder="Username" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       value="admin">
                <input type="password" name="password" placeholder="Password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       value="securepassword123">
                
                <button type="submit" name="login"
                        class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-indigo-700 transition duration-150 transform hover:scale-[1.01]">
                    Log In
                </button>
            </form>
            
            <!-- Link back to public view -->
            <div class="mt-4 text-center">
                <a href="index.php" class="text-sm text-indigo-600 hover:text-indigo-800 transition duration-150">&laquo; Back to Public View</a>
            </div>
            
            <!-- Error Message -->
            {$error_html}
        </div>
HTML; // Correctly un-indented closing delimiter.
    render_footer();
}

function render_post_form($db, $action, $post_id = null) {
    $post = ['title' => '', 'content' => '', 'id' => null];
    $is_editing = $post_id !== null;

    if ($is_editing) {
        $stmt = $db->prepare("SELECT title, content, id FROM posts WHERE id = :id");
        $stmt->execute([':id' => $post_id]);
        $fetched_post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched_post) {
            $post = $fetched_post;
        } else {
            // Post not found
            header('Location: index.php?mode=admin');
            exit;
        }
    }
    
    $form_title = $is_editing ? "Edit Post: {$post['title']}" : "Create New Post";
    $submit_text = $is_editing ? "Update Post" : "Publish Post";
    $target_action = $is_editing ? 'update_post' : 'create_post';
    $hidden_id_input = $is_editing ? "<input type='hidden' name='id' value='{$post['id']}'>" : '';

    render_header($form_title);
    echo <<<HTML
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-6 border-b pb-4">{$form_title}</h2>
            
            <form method="post" action="index.php?mode=admin&action={$target_action}&id={$post_id}" class="space-y-6">
                {$hidden_id_input}
                
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="title" name="title" required value="{$post['title']}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (Supports basic Markdown)</label>
                    <textarea id="content" name="content" required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{$post['content']}</textarea>
                </div>

                <div class="flex justify-between items-center">
                    <a href="index.php?mode=admin" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                        &laquo; Back to Post List
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-150 transform hover:scale-[1.01]">
                        {$submit_text}
                    </button>
                </div>
            </form>
        </div>
HTML;
    render_footer();
}

function render_admin_list($db) {
    render_header("Admin Panel - Posts");

    echo "<h2 class='text-4xl font-extrabold text-gray-900 mb-8'>Post Management</h2>";
    echo "<div class='flex justify-between items-center mb-6'>";
    echo "<a href='index.php?mode=admin&action=create' class='px-5 py-2 bg-green-500 text-white font-semibold rounded-lg shadow-lg hover:bg-green-600 transition duration-150 transform hover:scale-[1.01]'>
            + Create New Post
          </a>";
    echo "<a href='index.php?action=logout' class='text-sm text-gray-500 hover:text-red-600 transition duration-150'>Logout</a>";
    echo "</div>";

    // Fetch all posts for admin view
    $stmt = $db->query("SELECT id, title, published_at, is_published FROM posts ORDER BY published_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($posts)) {
        echo "<p class='text-gray-500 text-center py-10 border rounded-lg bg-white'>No posts found. Time to create your first one!</p>";
    } else {
        echo "<div class='space-y-4'>";
        foreach ($posts as $post) {
            $status_color = $post['is_published'] ? 'text-green-600' : 'text-yellow-600';
            $status_text = $post['is_published'] ? 'Published' : 'Draft';
            
            echo "<div class='bg-white p-5 rounded-xl shadow-lg flex justify-between items-center border border-gray-100'>";
            echo "<div>";
            echo "<h3 class='text-xl font-semibold text-gray-800'>{$post['title']}</h3>";
            echo "<p class='text-sm text-gray-500'>Published: {$post['published_at']}</p>";
            echo "</div>";
            echo "<div class='flex items-center space-x-4'>";
            echo "<span class='text-xs font-bold {$status_color}'>{$status_text}</span>";
            echo "<a href='index.php?mode=admin&action=edit&id={$post['id']}' class='text-indigo-600 hover:text-indigo-800 font-medium'>Edit</a>";
            echo "<a href='index.php?mode=admin&action=delete&id={$post['id']}' 
                       onclick=\"return confirm('Are you sure you want to delete \'{$post['title']}\'?')\" 
                       class='text-red-600 hover:text-red-800 font-medium'>Delete</a>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    }

    render_footer();
}

function render_public_view($db) {
    render_header("The Dev Blog - Home");

    echo "<h1 class='text-5xl font-extrabold text-gray-900 mb-10 border-b pb-4'>Latest Posts</h1>";
    
    // Fetch all published posts
    $stmt = $db->query("SELECT p.title, p.content, p.published_at, u.username as author FROM posts p JOIN users u ON p.author_id = u.id WHERE p.is_published = 1 ORDER BY p.published_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($posts)) {
        echo "<p class='text-gray-500 text-center py-10'>No posts published yet. Check back soon!</p>";
    } else {
        echo "<div class='space-y-12'>";
        foreach ($posts as $post) {
            echo "<article class='bg-white p-8 rounded-xl shadow-2xl border border-gray-100'>";
            echo "<h2 class='text-3xl font-bold text-gray-800 mb-2'>{$post['title']}</h2>";
            echo "<p class='text-sm text-gray-500 mb-6'>By {$post['author']} on " . date('F j, Y', strtotime($post['published_at'])) . "</p>";
            // Use pre-wrap to preserve original formatting (line breaks) from the text area
            echo "<div class='prose max-w-none text-gray-700 whitespace-pre-wrap'>{$post['content']}</div>";
            echo "</article>";
        }
        echo "</div>";
    }

    render_footer();
}


// --- MAIN ROUTER ---

if ($mode === 'admin' && !$is_admin) {
    // Show Login Form
    render_login_form($db);
} elseif ($mode === 'admin' && $is_admin) {
    // Admin Views
    switch ($action) {
        case 'create':
            render_post_form($db, 'create_post');
            break;
        case 'edit':
            render_post_form($db, 'update_post', $post_id);
            break;
        case 'list':
        case 'update_post': // Redirect after successful update
        case 'create_post': // Redirect after successful creation
        case 'delete': // Redirect after successful deletion
        default:
            render_admin_list($db);
            break;
    }
} else {
    // Public View
    render_public_view($db);
}
