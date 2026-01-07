<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Keamanan - SSGold BSI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at top right, #fff7ed, #f8fafc);
        }
        
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .pin-input {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid #f1f5f9;
        }

        .pin-input:focus {
            transform: scale(1.1) translateY(-5px);
            border-color: #f59e0b; /* Amber focus matching gold */
            background: white;
            box-shadow: 0 20px 25px -5px rgba(245, 158, 11, 0.1);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-10px); }
            40%, 80% { transform: translateX(10px); }
        }
        .error-shake { animation: shake 0.4s ease-in-out; }

        .bg-pattern {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            opacity: 0.3;
            background-image: radial-gradient(#f59e0b 0.5px, transparent 0.5px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-pattern"></div>

    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-48 h-48 bg-white rounded-full shadow-2xl shadow-amber-200/50 mb-4 border-4 border-white overflow-hidden transition-transform hover:scale-105 duration-500">
                <img src="logo.png" alt="Logo SSGold" class="w-full h-full object-contain p-1">
            </div>
            
            <p class="text-amber-700 font-bold tracking-widest uppercase text-[10px]">Sistem Autentikasi Keamanan SSGold</p>
        </div>

        <div id="loginCard" class="bg-white/90 backdrop-blur-xl rounded-[3rem] shadow-2xl shadow-slate-300/50 p-10 border border-white">
            <div class="flex justify-between items-center mb-10">
                <div class="text-left">
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Verifikasi PIN</h2>
                    <p class="text-slate-400 text-sm">Masukkan 6-digit kode akses</p>
                </div>
                <button onclick="togglePinVisibility()" class="p-3 bg-slate-100 hover:bg-amber-50 rounded-2xl text-slate-500 hover:text-amber-600 transition-colors" title="Lihat/Sembunyikan PIN">
                    <svg id="eyeIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="eyePath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>

            <div class="flex justify-between gap-3" id="pinInputs">
                <input type="password" inputmode="numeric" maxlength="1" class="pin-input w-full h-16 text-center text-2xl font-bold bg-slate-50 rounded-2xl outline-none focus:bg-white" autofocus>
                <input type="password" inputmode="numeric" maxlength="1" class="pin-input w-full h-16 text-center text-2xl font-bold bg-slate-50 rounded-2xl outline-none focus:bg-white">
                <input type="password" inputmode="numeric" maxlength="1" class="pin-input w-full h-16 text-center text-2xl font-bold bg-slate-50 rounded-2xl outline-none focus:bg-white">
                <input type="password" inputmode="numeric" maxlength="1" class="pin-input w-full h-16 text-center text-2xl font-bold bg-slate-50 rounded-2xl outline-none focus:bg-white">
                <input type="password" inputmode="numeric" maxlength="1" class="pin-input w-full h-16 text-center text-2xl font-bold bg-slate-50 rounded-2xl outline-none focus:bg-white">
                <input type="password" inputmode="numeric" maxlength="1" class="pin-input w-full h-16 text-center text-2xl font-bold bg-slate-50 rounded-2xl outline-none focus:bg-white">
            </div>

            <button onclick="checkPinManual()" class="w-full mt-10 bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-amber-200 transition-all active:scale-95">
                Masuk ke Sistem
            </button>

            <div id="statusMessage" class="h-4 mt-6 text-center text-[10px] font-bold transition-all uppercase tracking-[0.2em]"></div>
        </div>

        <div class="mt-10 text-center">
            <p class="text-slate-400 text-xs font-medium italic">"Akses aman, transaksi nyaman."</p>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('#pinInputs input');
        const card = document.getElementById('loginCard');
        const statusMsg = document.getElementById('statusMessage');
        const eyeIcon = document.getElementById('eyeIcon');
        let isVisible = false;

        function togglePinVisibility() {
            isVisible = !isVisible;
            inputs.forEach(input => {
                input.type = isVisible ? "text" : "password";
            });
            
            if (isVisible) {
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>`;
                eyeIcon.classList.add('text-amber-600');
            } else {
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
                eyeIcon.classList.remove('text-amber-600');
            }
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value !== "" && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                if (Array.from(inputs).every(i => i.value !== "")) {
                    validatePin();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === "" && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        function validatePin() {
            const pinValue = Array.from(inputs).map(i => i.value).join('');
            const correctPin = "123456";

            statusMsg.innerText = "Memverifikasi Akses...";
            statusMsg.className = "h-4 mt-6 text-center text-[10px] font-bold text-amber-600 tracking-[0.2em]";

            setTimeout(() => {
                if (pinValue === correctPin) {
                    window.location.href = 'index.html';
                } else {
                    card.classList.add('error-shake');
                    statusMsg.innerText = ""; 
                    
                    setTimeout(() => {
                        card.classList.remove('error-shake');
                        inputs.forEach(i => i.value = "");
                        inputs[0].focus();
                    }, 500);
                }
            }, 600);
        }

        function checkPinManual() {
            if (Array.from(inputs).some(i => i.value === "")) {
                statusMsg.innerText = "PIN Belum Lengkap";
                statusMsg.className = "h-4 mt-6 text-center text-[10px] font-bold text-amber-500 tracking-[0.2em]";
            } else {
                validatePin();
            }
        }
    </script>
</body>
</html>