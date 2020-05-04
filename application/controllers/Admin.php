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
}
