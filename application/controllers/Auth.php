<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

  public function __construct()
  {
    parent::__construct(); // untuk memanggil construct CI
    $this->load->library('form_validation');
  }

  public function index(){

    // cegah agar user tidak bisa logout ketika sudah login
    if($this->session->userdata('email')){
      redirect('user');
    }

    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');

    if($this->form_validation->run() == false){
      $data['title'] = "Login Page";
      $this->load->view('templates/auth_header', $data);
      $this->load->view('auth/login');
      $this->load->view('templates/auth_footer');
    }else{
      // ketika validasi berhasil
      $this->_login(); // ini methode private
    }
  }

  private function _login(){
    $email = $this->input->post('email');
    $password = $this->input->post('password');

    $user = $this->db->get_where('user', ['email'=> $email])->row_array();
    // var_dump($user);die;

    // jika usernya ada
    if($user){ 
      // jika usernya aktif
      if($user['is_active'] == 1){
        // cek password
        if(password_verify($password, $user['password'])){
          $data = [
            'email' => $user['email'],
            'role_id' => $user['role_id']
          ];
          $this->session->set_userdata($data); // simpan ke dalam session
          // cek role_id (user atau admin)
          if($user['role_id'] == 1){
            redirect('admin');
          }else{
            redirect('user');
          }

        }else{
          $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
          Wrong password!
          </div>');
          redirect('auth');
        }

      }else{
        $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
        This Email has not been activated!
        </div>');
        redirect('auth');
      }

    }else{
      $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
      Email is not registered!
      </div>');
      redirect('auth');
    }

  }
  
  public function registration(){

    if ($this->session->userdata('email')) {
      redirect('user');
    }

    $this->form_validation->set_rules('name', 'Name', 'required|trim');
    $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
      'is_unique' => 'this user has already registered'
    ]); // unique untuk cek sudah ada belum di database
    $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
      'matches' => 'password dont match',
      'min_length' => 'password too short'
    ]); // min panjang 3, agar cocok dengan password 2
    $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]'); // min panjang 3, agar cocok dengan password 2

    if($this->form_validation->run() == false){
      $data['title'] = "WPU - Registration";
      $this->load->view('templates/auth_header', $data);
      $this->load->view('auth/registration');
      $this->load->view('templates/auth_footer');
    }else{
      // echo "data berhasil ditambahkan";
      $data = [
        'name' => htmlspecialchars($this->input->post('name'), true),
        'email' => htmlspecialchars($this->input->post('email'), true),
        'image' => 'default.png',
        'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
        'role_id' => 2,
        'is_active' => 1,
        'date_created' => time()
      ];

      // insert ke db
      $this->db->insert('user', $data);
      $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
      Congratulations, your account has been created, <br> please login!
      </div>');
      redirect('auth');
    }
  }


  public function logout(){
    $this->session->unset_userdata('email');
    $this->session->unset_userdata('role_id');
    $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
    You have been logout! 
    </div>');
    redirect('auth');
  }


  public function blocked(){
    $data['title'] = 'Access Blocked';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    $this->load->view('templates/header', $data);
    $this->load->view('auth/blocked');
    $this->load->view('templates/footer');
  }






}
