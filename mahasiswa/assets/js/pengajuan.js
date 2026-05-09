// File Upload Display Logic
const fileInput = document.getElementById('berkasProposal');
const fileNameDisplay = document.getElementById('fileName');

if (fileInput && fileNameDisplay) {
    fileInput.addEventListener('change', function(e) {
        if(this.files && this.files.length > 0) {
            const file = this.files[0];
            if(file.type === "application/pdf") {
                fileNameDisplay.textContent = file.name;
                fileNameDisplay.style.color = "#2563eb";
            } else {
                fileNameDisplay.textContent = "Error: Harus file PDF";
                fileNameDisplay.style.color = "#dc3545";
                this.value = "";
            }
        } else {
            fileNameDisplay.textContent = "Pilih file PDF atau seret ke sini";
            fileNameDisplay.style.color = "#475569";
        }
    });
}

// Form Submit Logic dihandle oleh PHP
// Toggle Jenis Perusahaan Logic
const radioTersedia = document.getElementById('mitraTersedia');
const radioBaru = document.getElementById('perusahaanBaru');
const opsiMitraTersedia = document.getElementById('opsiMitraTersedia');
const fieldNamaPerusahaan = document.getElementById('fieldNamaPerusahaan');
const selectMitra = document.getElementById('selectMitra');
const inputNamaPerusahaan = document.getElementById('inputNamaPerusahaan');
const inputsDetail = ['inputAlamat', 'inputProvinsi', 'inputKota', 'inputKecamatan', 'inputKodePos'];

function toggleJenisPerusahaan() {
    if (radioTersedia && radioTersedia.checked) {
        opsiMitraTersedia.style.display = 'flex';
        fieldNamaPerusahaan.style.display = 'none';
        selectMitra.required = true;
        inputNamaPerusahaan.required = false;
        inputsDetail.forEach(id => {
            const el = document.getElementById(id);
            el.readOnly = true;
            el.style.backgroundColor = '#f8fafc';
            el.style.color = '#64748b';
        });
        selectMitra.dispatchEvent(new Event('change'));
    } else {
        opsiMitraTersedia.style.display = 'none';
        fieldNamaPerusahaan.style.display = 'block';
        selectMitra.required = false;
        inputNamaPerusahaan.required = true;
        inputsDetail.forEach(id => {
            const el = document.getElementById(id);
            el.readOnly = false;
            el.style.backgroundColor = '#fff';
            el.style.color = '#1e293b';
            el.value = '';
        });
        inputNamaPerusahaan.value = '';
    }
}

if (radioTersedia) radioTersedia.addEventListener('change', toggleJenisPerusahaan);
if (radioBaru) radioBaru.addEventListener('change', toggleJenisPerusahaan);

if (selectMitra) {
    selectMitra.addEventListener('change', function() {
        if (this.selectedIndex > 0) {
            const option = this.options[this.selectedIndex];
            document.getElementById('inputAlamat').value = option.getAttribute('data-alamat');
            document.getElementById('inputProvinsi').value = option.getAttribute('data-provinsi');
            document.getElementById('inputKota').value = option.getAttribute('data-kota');
            document.getElementById('inputKecamatan').value = option.getAttribute('data-kecamatan');
            document.getElementById('inputKodePos').value = option.getAttribute('data-kodepos');
        } else {
            inputsDetail.forEach(id => document.getElementById(id).value = '');
        }
    });
}

// Initialize on load
toggleJenisPerusahaan();
