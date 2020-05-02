    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-code"></i>
        </div>
        <div class="sidebar-brand-text mx-3">WPU Admin</div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider">


      <!-- query dari menu, tampilkan menu yang boleh diakses oleh user/admin -->
      <!-- melakukan join dari ketiga table -->
      <?php

      $role_id = $this->session->userdata('role_id');
      $queryMenu =  "SELECT user_menu.id, menu
                      FROM user_menu JOIN user_access_menu
                        ON user_menu.id = user_access_menu.menu_id
                    WHERE user_access_menu.role_id = $role_id ORDER BY user_access_menu.menu_id ASC";

      $menu = $this->db->query($queryMenu)->result_array();
      // var_dump($menu);die;


      ?>

      <!-- looping menu -->
      <?php foreach ($menu as $m) : ?>
        <!-- Heading -->
        <div class="sidebar-heading">
          <?= $m['menu']; ?>
        </div>

        <!-- siapkan submenu sesuai menu -->
        <?php

        $menu_id = $m['id'];
        // $querySubMenu = "SELECT *
        //                   FROM user_sub_menu JOIN user_menu 
        //                     ON user_sub_menu.id = user_menu.id
        //                 WHERE user_sub_menu.menu_id = $menu_id
        //                     AND user_sub_menu.is_active = 1";
        // ! cara yang tidak pake join
        $querySubMenu = "SELECT * FROM user_sub_menu 
                        WHERE menu_id = $menu_id
                            AND is_active = 1";
        $subMenu = $this->db->query($querySubMenu)->result_array();
        ?>

        <?php foreach ($subMenu as $sm) : ?>
          <!-- Nav Item - Dashboard -->
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url($sm['url']) ?>">
              <i class="<?= $sm['icon'] ?>"></i>
              <span><?= $sm['title'] ?></span></a>
          </li>
        <?php endforeach; ?>

          <hr class="sidebar-divider">

      <?php endforeach; ?>

      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('auth/logout') ?>" data-toggle="modal" data-target="#logoutModal">
          <i class="fas fa-fw fa-sign-out-alt"></i> <!-- fa-fw => fix width -->
          <span>Logout</span></a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->