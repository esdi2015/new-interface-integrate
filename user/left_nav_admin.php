<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
?>
<ul class="sidebar-nav">
    <li class="sidebar-brand" style="color: silver;">
        Index
    </li>
    <li>
        <a href='index_admin.php'>
            Dashboard
        </a>
    </li>
    <li>
        <a href='leads_statuses.php'>
            Leads
        </a>
    </li>
    <li class="sidebar-brand" style="color: silver;">
        Administration
    </li>
    <li>
        <a href='admin_configuration.php'>
            Configuration
        </a>
    </li>
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
    <!--<li>
                <a href='admin_permissions.php'>
                  Manage permissions
                </a>
              </li>-->
    <li class="sidebar-brand" style="color: silver;">
        Current user
    </li>
    <li>
        <a href='user_settings.php'>
            User Settings
        </a>
    </li>
    <li>
        <a href='<?php echo $websiteUrl; ?>'>
            My history
        </a>
    </li>
    <li>
        <a href='logout.php'>
            Logout
        </a>
    </li>
</ul>

