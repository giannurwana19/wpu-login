<?php

// * kita tidak bisa begitu saja panggil this, seperti kita panggil di controller
// ! jadi wpu_helper ini tidak masuk ke mvc nya CI, karena dia tidak mengenali struktur codeigniternya
// * makanya kita harus instance codeigniter baru di dalam helper ini
// todo caranya kita panggil instance ci nya yaitu get_instance, untuk memanggil library codeigniter di dalam fungsi ini

function is_logged_in(){
  $ci = get_instance();
  if(!$ci->session->userdata('email')){
    redirect('auth');
  }else{
    $role_id = $ci->session->userdata('role_id');
    $menu = $ci->uri->segment(1);

    // query tabel user_menu berdasarkan nama $menu untuk mendapatkan menu_id
    $queryMenu = $ci->db->get_where('user_menu', ['menu' => $menu])->row_array();
    $menuId = $queryMenu['id'];

    $userAccess = $ci->db->get_where('user_access_menu', [
      'role_id'  => $role_id,
      'menu_id' => $menuId 
      ]);

      if($userAccess->num_rows() < 1){
        redirect('auth/blocked');
      }

  }
}