<?php
require_once __DIR__ . '/../../classes/Auth.php';
use App\Auth;

$auth = new Auth();
$auth->requireAuth();
$user = $auth->getCurrentUser();

// Timezone setup (adjust as needed)
date_default_timezone_set('UTC'); // Change to your timezone if needed

// Date and time formatting
$time = date('h:i A');
$day = date('l');
$date = date('d, M Y');
$dayOfYear = date('z') + 1; // Day of year (z is 0-based)
$fullDate = date('d, M Y');
$year = date('Y');
?>
<style>
    .welcome-container {
        display: flex;
        gap: 2rem;
        max-width: 100%;
        margin: 1rem auto;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .user-card {
        background: #f6e7c1;
        padding: 1.5rem;
        border-radius: 1rem;
        min-width: 260px;
        box-shadow: 0 2px 8px #0001;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        background: #111;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.3rem;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .user-info > div:first-child {
        font-weight: bold;
        font-size: 1.1rem;
    }

    .user-info > div:last-child {
        color: #555;
        font-size: 0.97rem;
    }

    .logout-btn {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid #444;
        border-radius: 7px;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-left: auto;
    }

    .logout-btn > span {
        display: inline-block;
        transform: rotate(180deg);
        font-size: 1.1rem;
    }

    .datetime-card {
        background: #f6e7c1;
        padding: 1.5rem;
        border-radius: 1rem;
        min-width: 320px;
        width: 100%;
        box-shadow: 0 2px 8px #0001;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2.5rem;
        flex: 1;
    }

    .datetime-info {
        text-align: left;
        flex: 1;
    }

    .datetime-info > div:first-child {
        font-weight: bold;
        font-size: 1.2rem;
    }

    .datetime-info > div:last-child {
        color: #666;
        font-size: 0.97rem;
    }

    .datetime-info:last-child {
        text-align: right;
    }
    @media (max-width: 768px) {
        .welcome-container {
            flex-direction: column;
        }
</style>

<div class="welcome-container">
    <!-- User Card -->
    <div class="user-card">
        <div class="user-avatar"><?= substr($user['first_name'], 0, 1) ?></div>
        <div class="user-info">
            <div>Welcome</div>
            <div><?= $user['first_name'] ?></div>
        </div>
        <form method="post" action="logout.php" style="margin-left: auto;">
            <button type="submit" class="logout-btn">
                <span><i class="fas fa-sign-out-alt"></i>&nbsp;</span> Sign out
            </button>
        </form>
    </div>
    <!-- Date/Time Card -->
    <div class="datetime-card">
        <div class="datetime-info">
            <div><?= $time ?></div>
            <div><?= $day ?></div>
        </div>
        <div class="datetime-info">
            <div><?= $date ?></div>
            <div>Day <?= $dayOfYear ?> of the year</div>
        </div>
    </div>
</div>