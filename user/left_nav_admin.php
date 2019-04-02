<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}

function managerSubmenu($user_permissions) {
    echo "
    <li class='sidebar-brand' style='color: silver;'>
        Administration
    </li>
    ";

    if ($user_permissions == 2) {
        echo "
        <li>
            <a href='admin_configuration.php'>
                Configuration
            </a>
        </li>
        ";
    }

    echo "
    <li>
        <a href='register.php'>
            Add user
        </a>
    </li>
    <li>
        <a href='admin_users.php'>
            Manage users
        </a>
    </li>
    <li>
        <a href='new_campaign.php'>
            Add campaign
        </a>
    </li>
    <li>
        <a href='admin_campaigns.php'>
            Manage campaigns
        </a>
    </li>
    ";
}

function adminSubmenuTop($user_permissions) {
    echo "
    <li class='sidebar-brand' style='color: silver;'>
        Index
    </li>
    <li>
        <a href='index_admin.php'>
            Dashboard
        </a>
    </li>
    ";

    if (in_array($user_permissions, array(1,2))) {
        echo "
        <li>
            <a href='leads_statuses.php'>
                Leads
            </a>
        </li>
        ";
    }
}

function adminSubmenuBottom($user_permissions) {
    global $websiteUrl;

    if (in_array($user_permissions, array(2))) {
        echo "
        <!--<li>
            <a href='admin_permissions.php'>
                Manage permissions
            </a>
        </li>-->
        <li class='sidebar-brand' style='color: silver;'>
            Current user
        </li>
        <li>
            <a href='user_settings.php'>
                User Settings
            </a>
        </li>
        ";
    }

    if (in_array($user_permissions, array(1,2))) {
        echo "
        <li>
            <a href='".$websiteUrl."'>
                Uploads
            </a>
        </li>
        ";
    }

    if (in_array($user_permissions, array(1,2,3))) {
        echo "
        <li>
            <a href='logout.php'>
                Logout
            </a>
        </li>
        ";
    }
}

// $is_user_admin = $loggedInUser->checkPermission(array(2));

if ($loggedInUser->checkPermission(array(2))) {
    ?>
    <ul class="sidebar-nav">
        <?php
        adminSubmenuTop(2);
        managerSubmenu(2);
        adminSubmenuBottom(2);
        ?>
    </ul>
<?php
} elseif ($loggedInUser->checkPermission(array(3))) {
    ?>
    <ul class="sidebar-nav">
        <?php
        adminSubmenuTop(3);
        managerSubmenu(3);
        adminSubmenuBottom(3);
        ?>
    </ul>
<?php
} elseif ($loggedInUser->checkPermission(array(1))) {
    ?>
    <ul class="sidebar-nav">
        <?php
        adminSubmenuTop(1);
        //managerSubmenu(1);
        adminSubmenuBottom(1);
        ?>
    </ul>
<?php
}
?>
