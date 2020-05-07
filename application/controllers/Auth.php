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
  
  public function registration()
  {

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
      $email = $this->input->post('email');
      $data = [
        'name' => htmlspecialchars($this->input->post('name'), true),
        'email' => htmlspecialchars($email, true),
        'image' => 'default.png',
        'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
        'role_id' => 2,
        'is_active' => 0, // buat jadi 0, agar tidak active dan tidak bisa login
        'date_created' => time()
      ];

      // * siapkan token berupa bilangan random
      // random_bytes = membangkitkan bilangan random
      // base64_encode() = untuk menterjemahkan kode agar bisa dikenal mysql
      $token = base64_encode(random_bytes(32)); // nanti bilangan ini yang akan dikirim ke email
      $user_token = [
        'email' => $email,
        'token' => $token,
        'date_created' => time()
      ];

      // var_dump($token);die;

      // insert ke db
      $this->db->insert('user', $data);
      $this->db->insert('user_token', $user_token); // insert ke table token

      // kirim email
      $this->_sendEmail($token, 'verify'); // kirim parameter token ke email untuk verifikasi password 

      $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
      Congratulations, your account has been created, <br> please activate your account!
      </div>');
      redirect('auth');
    }
  }

  private function _sendEmail($token, $type){
    $config = [
      'protocol' => 'smtp', // protokolnya apa
      'smtp_host' => 'ssl://smtp.googlemail.com', // kita tulis google disini
      'smtp_user' => 'giansmpn2sepatan@gmail.com', // email pengirim
      'smtp_pass' => 'ginjalhewan', //  passwordnya
      'smtp_port' => 465, // port google
      'mailtype' => 'html', // tipe emailnya, karena kita mau nulis ada link nya 
      'charset' => 'utf-8', // karakter setnya
      'newline' => "\r\n" // kalo ngga pake ini ngga mau ngirim nanti
    ];

    // panggil library email codeigniter
    $this->email->initialize($config);

    $this->email->from('giansmpn2sepatan@gmail.com', 'Gian Nurwana'); // (email pengirim, nama lengkap (alias))
    $this->email->to($this->input->post('email')); // email penerima

    if($type == 'verify'){
      $this->email->subject('Account Verification'); // subject nya
      $this->email->message('Click this link to verify your account : <a href="' . base_url() . 'auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Activate</a>'); // isi email
      // ! urlencode = untuk menterjemahkan kode yang tidak rama url (+ =)
    }else if($type == 'forgot'){
      $this->email->subject('Reset Password'); // subject nya
      $this->email->message('Click this link to reset your password : <a href="' . base_url() . 'auth/resetpassword?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Reset Password</a>');
    }


    // $this->email->send(); // kirim emailnya
    // ! percobaan kirim email
    if ($this->email->send()) {
      return true;
    } else {
      echo $this->email->print_debugger(); // tampilkan errornya
      die;
    }


  }

  public function verify(){ // fungsi ini akan melakukan verifikasi terhadap link yang dikirim lewat email
    // kalo email & dan token nya ada di database, kita akan ubah di table user is_active nya jadi 1, agar bisa login
    // * ambil email & token di url
    $email = $this->input->get('email');
    $token = $this->input->get('token');

    // ? ambil user berdasarkan user db
    $user = $this->db->get_where('user', ['email' => $email])->row_array();

    if($user){
      // query token nya dan cocokkan dengan di url
      $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

      if($user_token){
        // buat batas waktu
        if(time() - $user_token['date_created'] < (60*60*24)){ // lebih kecil dari 1 hari){
          // user boleh daftar
          $this->db->set('is_active', 1);
          $this->db->where('email', $email);
          $this->db->update('user');

          $this->db->delete('user_token', ['email' => $email]);

          $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
          ' . $email . ' has been activated, <br>please login!
          </div>');
          redirect('auth');
        }else{
          // kalo lewat batas waktu hapus dari database
          $this->db->delete('user', ['email' => $email]);
          $this->db->delete('user_token', ['email' => $email]);

          $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
          Token invalid!
          </div>');
          redirect('auth');
        }


      }else{
        $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
        Token invalid!
        </div>');
          redirect('auth');
      }

    }else{
      $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
      Account activation failed! Wrong email
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


  public function forgotPassword(){

    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

    if($this->form_validation->run() == false){
          $data['title'] = "forgot Password";
          $this->load->view('templates/auth_header', $data);
          $this->load->view('auth/forgot_password');
          $this->load->view('templates/auth_footer');
    }else{
      $email = $this->input->post('email');
      $user = $this->db->get_where('user', ['email' => $email, 'is_active' => 1])->row_array();

      if($user){
        $token = base64_encode(random_bytes(32));
        $user_token = [
          'email' => $email,
          'token' => $token,
          'date_created' => time()
        ];

        $this->db->insert('user_token', $user_token);
        $this->_sendEmail($token, 'forgot');
        $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
        Please Check your email to reset your password!
        </div>');
        redirect('auth/forgotPassword');

        
      }else{
        $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
        Email is not registered or activated!
        </div>');
        redirect('auth/forgotPassword');
      }
    }


  }

  public function resetpassword(){
    $email = $this->input->get('email');
    $token = $this->input->get('token');

    // * contoh kalo mau pake model
    // $user = $this->user->getUserByEmail();

    $user = $this->db->get_where('user', ['email' => $email])->row_array();

    if($user){
      $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

      if($user_token){
        $this->session->set_userdata('reset_email', $email);
        $this->changePassword();
      }else{
        $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
        Reset password falied! Wrong token
        </div>');
        redirect('auth');
      }

    }else{
      $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
      Reset password falied! Wrong email
      </div>');
      redirect('auth');
    }


  }


  public function changePassword(){

    // agar method ini tidak bisa diakses (diubah password) tanpa lewat email
    if(!$this->session->userdata('reset_email')){
      redirect('auth');
    }

    $this->form_validation->set_rules('password1', 'Password', 'trim|required|matches[password2]|min_length[3]');
    $this->form_validation->set_rules('password2', 'Password', 'trim|required|matches[password1]|min_length[3]');

    if($this->form_validation->run() == false){
      $data['title'] = "Change Password";
      $this->load->view('templates/auth_header', $data);
      $this->load->view('auth/change_password');
      $this->load->view('templates/auth_footer');

    }else{
      $password = password_hash($this->input->post('password1'), PASSWORD_DEFAULT);
      $email = $this->session->userdata('reset_email');

      $this->db->set('password', $password);
      $this->db->where('email', $email);
      $this->db->update('user');

      $this->session->unset_userdata('reset_email');

      $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
      Password has been changed! please login!
      </div>');
      redirect('auth');
    }

  }






}
