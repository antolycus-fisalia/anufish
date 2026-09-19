<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Anufish - Profil Pengguna</title>

  <style>
    :root {
      --bg: #f4f9fc;
      --surface: #ffffff;
      --text: #17324a;
      --muted: #718593;
      --line: #dce7ee;
      --blue: #1268b3;
      --cyan: #09b5c6;
      --cyan-soft: #e8f8fa;
      --success: #18875f;
      --danger: #c94d57;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    button,
    input {
      font: inherit;
    }

    .app {
      display: grid;
      grid-template-columns: 250px 1fr;
      min-height: 100vh;
    }

    .sidebar {
      background: white;
      border-right: 1px solid var(--line);
      padding: 20px;
    }

    .logo {
      font-size: 24px;
      font-weight: bold;
      color: var(--blue);
      margin-bottom: 30px;
    }

    .nav a {
      display: block;
      padding: 12px 14px;
      margin-bottom: 6px;
      border-radius: 10px;
      color: #607888;
      text-decoration: none;
      font-weight: 600;
    }

    .nav a:hover,
    .nav a.active {
      background: var(--cyan-soft);
      color: var(--blue);
    }

    .nav a.logout {
      color: var(--danger);
      margin-top: 20px;
    }

    .main {
      min-width: 0;
    }

    .topbar {
      height: 70px;
      background: white;
      border-bottom: 1px solid var(--line);
      display: flex;
      align-items: center;
      padding: 0 30px;
    }

    .topbar h1 {
      margin: 0;
      font-size: 20px;
    }

    .content {
      padding: 30px;
      max-width: 1100px;
      margin: auto;
    }

    .page-title {
      margin-bottom: 20px;
    }

    .page-title h2 {
      margin: 0;
      font-size: 30px;
    }

    .page-title p {
      color: var(--muted);
      margin-top: 6px;
    }

    .grid {
      display: grid;
      grid-template-columns: 0.85fr 1.15fr;
      gap: 20px;
    }

    .card {
      background: white;
      border: 1px solid var(--line);
      border-radius: 18px;
      padding: 24px;
    }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .avatar {
      width: 85px;
      height: 85px;
      border-radius: 24px;
      background: linear-gradient(
        135deg,
        var(--blue),
        var(--cyan)
      );
      color: white;
      display: grid;
      place-items: center;
      font-size: 26px;
      font-weight: bold;
      overflow: hidden;
    }

    .avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .profile-header h3 {
      margin: 0 0 4px;
      font-size: 22px;
    }

    .profile-header p {
      margin: 0;
      color: var(--muted);
    }

    .status {
      display: inline-block;
      margin-top: 8px;
      padding: 6px 10px;
      border-radius: 999px;
      background: #e7f7ef;
      color: var(--success);
      font-size: 12px;
      font-weight: bold;
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-top: 22px;
    }

    .stat {
      background: #f9fcfe;
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 16px;
    }

    .stat strong {
      display: block;
      font-size: 22px;
      margin-bottom: 4px;
    }

    .stat span {
      color: var(--muted);
      font-size: 13px;
    }

    .form-card h3 {
      margin-top: 0;
    }

    .field {
      margin-bottom: 16px;
    }

    .field label {
      display: block;
      margin-bottom: 7px;
      font-size: 13px;
      font-weight: bold;
    }

    .field input {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid var(--line);
      border-radius: 11px;
      outline: none;
    }

    .field input:focus {
      border-color: var(--cyan);
    }

    .photo-row {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .photo-preview {
      width: 70px;
      height: 70px;
      border-radius: 18px;
      background: var(--cyan-soft);
      display: grid;
      place-items: center;
      overflow: hidden;
      font-weight: bold;
    }

    .photo-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .helper {
      margin-top: 6px;
      font-size: 12px;
      color: var(--muted);
    }

    .feedback {
      display: none;
      margin-bottom: 16px;
      padding: 12px 14px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
    }

    .feedback.success {
      display: block;
      background: #edf9f3;
      color: var(--success);
      border: 1px solid #cfeadb;
    }

    .feedback.error {
      display: block;
      background: #fff0f1;
      color: var(--danger);
      border: 1px solid #efc9cc;
    }

    .actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .btn {
      border: none;
      border-radius: 11px;
      padding: 11px 16px;
      font-weight: bold;
      cursor: pointer;
    }

    .btn-primary {
      color: white;
      background: linear-gradient(
        135deg,
        var(--blue),
        var(--cyan)
      );
    }

    .btn-secondary {
      background: white;
      border: 1px solid var(--line);
      color: #607888;
    }

    @media (max-width: 900px) {
      .app {
        grid-template-columns: 1fr;
      }

      .sidebar {
        display: none;
      }

      .grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 600px) {
      .content {
        padding: 20px 14px;
      }

      .stats {
        grid-template-columns: 1fr;
      }

      .actions {
        flex-direction: column-reverse;
      }

      .btn {
        width: 100%;
      }

      .photo-row {
        align-items: flex-start;
        flex-direction: column;
      }
    }
  </style>
</head>

<body>

  <div class="app">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        Anufish
      </div>

      <nav class="nav">
        <a href="#">
          Dashboard
        </a>

        <a href="#">
          Scan Ikan
        </a>

        <a href="#">
          Riwayat Scan
        </a>

        <a href="#">
          Artikel Saya
        </a>

        <a href="#" class="active">
          Profil
        </a>

        <a href="#" class="logout">
          Logout
        </a>
      </nav>
    </aside>

    <!-- Main -->
    <main class="main">

      <header class="topbar">
        <h1>Profil Pengguna</h1>
      </header>

      <section class="content">

        <div class="page-title">
          <h2>Profil Pengguna</h2>

          <p>
            Lihat dan ubah informasi akun Anda.
          </p>
        </div>

        <div
          id="feedback"
          class="feedback">
        </div>

        <div class="grid">

          <!-- Informasi Profil -->
          <section class="card">

            <div class="profile-header">

              <div
                class="avatar"
                id="avatarMain">
                II
              </div>

              <div>
                <h3 id="displayName">
                  Istianto Ilham
                </h3>

                <p id="displayEmail">
                  user@example.com
                </p>

                <span class="status">
                  Akun Aktif
                </span>
              </div>

            </div>

            <div class="stats">

              <div class="stat">
                <strong>34</strong>
                <span>Riwayat Scan</span>
              </div>

              <div class="stat">
                <strong>12</strong>
                <span>Artikel</span>
              </div>

              <div class="stat">
                <strong>9</strong>
                <span>Disetujui</span>
              </div>

            </div>

          </section>

          <!-- Form Edit -->
          <section class="card form-card">

            <h3>Edit Profil</h3>

            <form id="profileForm">

              <div class="field">

                <label for="name">
                  Nama Lengkap
                </label>

                <input
                  type="text"
                  id="name"
                  value="Istianto Ilham"
                  required
                >

              </div>

              <div class="field">

                <label for="email">
                  Email
                </label>

                <input
                  type="email"
                  id="email"
                  value="user@example.com"
                  required
                >

              </div>

              <div class="field">

                <label for="photo">
                  Foto Profil
                </label>

                <div class="photo-row">

                  <div
                    class="photo-preview"
                    id="photoPreview">
                    II
                  </div>

                  <div>

                    <input
                      type="file"
                      id="photo"
                      accept="image/png,image/jpeg,image/jpg"
                    >

                    <div class="helper">
                      JPG, JPEG, PNG • Maksimal 5 MB
                    </div>

                  </div>

                </div>

              </div>

              <div class="actions">

                <button
                  type="button"
                  class="btn btn-secondary"
                  id="cancelButton">
                  Batal
                </button>

                <button
                  type="submit"
                  class="btn btn-primary">
                  Simpan Perubahan
                </button>

              </div>

            </form>

          </section>

        </div>

      </section>

    </main>

  </div>


  <script>
    const form =
      document.getElementById("profileForm");

    const nameInput =
      document.getElementById("name");

    const emailInput =
      document.getElementById("email");

    const displayName =
      document.getElementById("displayName");

    const displayEmail =
      document.getElementById("displayEmail");

    const feedback =
      document.getElementById("feedback");

    const photoInput =
      document.getElementById("photo");

    const photoPreview =
      document.getElementById("photoPreview");

    const avatarMain =
      document.getElementById("avatarMain");

    const cancelButton =
      document.getElementById("cancelButton");


    const originalData = {
      name: nameInput.value,
      email: emailInput.value
    };


    function showFeedback(
      type,
      message
    ) {

      feedback.className =
        "feedback " + type;

      feedback.textContent =
        message;
    }


    form.addEventListener(
      "submit",
      function(event) {

        event.preventDefault();

        const name =
          nameInput.value.trim();

        const email =
          emailInput.value.trim();


        if (!name) {

          showFeedback(
            "error",
            "Nama tidak boleh kosong."
          );

          return;
        }


        if (
          !email ||
          !email.includes("@")
        ) {

          showFeedback(
            "error",
            "Email tidak valid."
          );

          return;
        }


        displayName.textContent =
          name;

        displayEmail.textContent =
          email;


        showFeedback(
          "success",
          "Profil berhasil diperbarui."
        );

      }
    );


    photoInput.addEventListener(
      "change",
      function() {

        const file =
          photoInput.files[0];


        if (!file) {
          return;
        }


        if (
          file.size >
          5 * 1024 * 1024
        ) {

          photoInput.value = "";

          showFeedback(
            "error",
            "Ukuran gambar maksimal 5 MB."
          );

          return;
        }


        const imageUrl =
          URL.createObjectURL(file);


        photoPreview.innerHTML =
          `<img src="${imageUrl}">`;


        avatarMain.innerHTML =
          `<img src="${imageUrl}">`;

      }
    );


    cancelButton.addEventListener(
      "click",
      function() {

        nameInput.value =
          originalData.name;

        emailInput.value =
          originalData.email;


        feedback.className =
          "feedback";

      }
    );
  </script>

</body>
</html>