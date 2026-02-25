<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>

  <!-- Tailwind CDN (kalau proyek sudah pakai Tailwind via Vite, hapus ini) -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white min-h-[100dvh] flex items-center justify-center p-4">
  <div class="w-full max-w-[950px] bg-white [box-shadow:0_2px_10px_-3px_rgba(14,14,14,0.3)] rounded-2xl overflow-hidden">
    <div class="flex items-center max-md:flex-col w-full gap-y-4">

      <!-- LEFT IMAGE -->
      <div class="hidden md:block md:max-w-[570px] w-full h-full">
        <div class="md:aspect-[7/10] bg-gray-50 relative before:absolute before:inset-0 before:bg-black/40 overflow-hidden w-full h-full">
          <img src="{{ asset('image/formloginimage.png') }}"
               class="w-full h-full object-cover"
               alt="Login Hotel">
          <div class="absolute inset-0 flex items-end justify-center">
            <div class="w-full bg-gradient-to-t from-black/50 via-black/50 to-transparent absolute bottom-0 p-6 max-md:hidden">
              <h1 class="text-white text-2xl font-semibold">Selamat Datang Kembali</h1>
              <p class="text-slate-300 text-[15px] font-medium mt-3 leading-relaxed">
                Akses Sistem Manajemen Booking Hotel
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT FORM -->
      <div class="w-full h-full px-8 lg:px-20 py-8 max-md:-order-1">
        <form class="md:max-w-md w-full mx-auto" method="POST" action="{{ route('login.proses') }}">
          @csrf

          <div class="mb-12">
            <img
              src="{{ asset('image/logo-login.png') }}"
              alt="Ayu Beach Inn"
              class="mx-auto mb-5 w-full max-w-[220px] object-contain"
            >
            <h3 class="text-4xl font-bold text-slate-900">Masuk</h3>
          </div>

          @if ($errors->any())
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
              <div class="font-semibold mb-2">Terjadi kesalahan:</div>
              <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- USERNAME  -->
          <div>
            <div class="relative flex items-center">
              <input
                name="username"
                type="text"
                required
                value="{{ old('username') }}"
                class="w-full text-sm border-b border-gray-300 focus:border-black pr-8 px-2 py-3 outline-none"
                placeholder="Masukan username"
              />
              <!-- icon user -->
              <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb"
                   class="w-[18px] h-[18px] absolute right-2" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
              </svg>
            </div>
          </div>

          <!-- PASSWORD -->
          <div class="mt-8">
            <div class="relative flex items-center">
              <input
                id="password"
                name="password"
                type="password"
                required
                class="w-full text-sm border-b border-gray-300 focus:border-black pr-10 px-2 py-3 outline-none"
                placeholder="Masukkan password"
              />

              <!-- eye icon -->
              <button type="button" onclick="togglePassword()"
                      class="absolute right-2 focus:outline-none">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg"
                    fill="#bbb" class="w-[18px] h-[18px]"
                    viewBox="0 0 128 128">
                  <path d="M64 104C22.127 104 1.367 67.496.504 65.943a4 4 0 0 1 0-3.887C1.367 60.504 22.127 24 64 24s62.633 36.504 63.496 38.057a4 4 0 0 1 0 3.887C126.633 67.496 105.873 104 64 104zM8.707 63.994C13.465 71.205 32.146 96 64 96c31.955 0 50.553-24.775 55.293-31.994C114.535 56.795 95.854 32 64 32 32.045 32 13.447 56.775 8.707 63.994zM64 88c-13.234 0-24-10.766-24-24s10.766-24 24-24 24 10.766 24 24-10.766 24-24 24zm0-40c-8.822 0-16 7.178-16 16s7.178 16 16 16 16-7.178 16-16-7.178-16-16-16z"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- SUBMIT -->
          <div class="mt-12">
            <button type="submit"
                    class="w-full shadow-xl py-2 px-4 text-[15px] font-medium tracking-wide rounded-md cursor-pointer text-white bg-[#854836] hover:bg-[#6f3a2e] focus:outline-none">
              Masuk
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.style.fill = '#000'; // aktif
      } else {
        passwordInput.type = 'password';
        eyeIcon.style.fill = '#bbb'; // normal
      }
    }
  </script>
</body>
</html>
