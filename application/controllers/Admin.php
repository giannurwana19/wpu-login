<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller{

  public function __construct() // function untuk mencegah user yg belum login
  {
    parent::__construct();
    is_logged_in();
    // if(!$this->session->userdata('email')){
    //   redirect('auth');
    // }

  }

  public function index(){

    $data['title'] = "Dashboard";
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    // echo "selamat datang " . $data['user']['name'];

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/index', $data);
    $this->load->view('templates/footer');
  }
  public function role(){

    $data['title'] = "Dashboard";
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    $data['role'] = $this->db->get('user_role')->result_array();
    // echo "selamat datang " . $data['user']['name'];

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/role', $data);
    $this->load->view('templates/footer');
  }

  public function roleAccess($role_id){

    $data['title'] = "Dashboard";
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    $data['role'] = $this->db->get_where('user_role', ['id' => $role_id])->row_array();
    // echo "selamat datang " . $data['user']['name'];

    $this->db->where('id !=', 1);
    $data['menu'] = $this->db->get('user_menu')->result_array();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/role_access', $data);
    $this->load->view('templates/footer');
  }


  public function changeAccess(){
    $menu_id = $this->input->post('menuId'); // diambil dari data menuId pada ajax
    $role_id = $this->input->post('roleId');

    $data = [
      'role_id' => $role_id,
      'menu_id' => $menu_id,
    ];

    $result = $this->db->get_where('user_access_menu', $data);

    if($result->num_rows() < 1){
      $this->db->insert('user_access_menu', $data);
    }else{
      $this->db->delete('user_access_menu', $data);
    }

    $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">Access Changed!</div>');

  }
}
