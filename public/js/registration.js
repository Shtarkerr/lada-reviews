const day = document.getElementById('day');
const month = document.getElementById('month');
const year = document.getElementById('year');
const dobInput = document.getElementById('dob');
const dobError = document.getElementById('dobError');
const blood = document.getElementById('blood');
const bloodError = document.getElementById('bloodError');
const resus = document.getElementById('resus');
const resusError = document.getElementById('resusError');
const sex = document.getElementById('sex');
const sexError = document.getElementById('sexError');
const submitBtn = document.getElementById('submitBtn');
const emailFormatError = document.getElementById('emailFormatError');
const emailError = document.getElementById('emailError');
const emailInput = document.getElementById('email');

// Дата рождения
function populateMonths() {
    month.innerHTML = '<option value="">Месяц</option>';
    for (let m = 1; m <= 12; m++) {
        const opt = document.createElement('option');
        opt.value = opt.text = m < 10 ? '0' + m : m;
        month.appendChild(opt);
    }
}

function populateYears() {
    year.innerHTML = '<option value="">Год</option>';
    const thisYear = new Date().getFullYear();
    for (let y = thisYear; y >= 1900; y--) {
        const opt = document.createElement('option');
        opt.value = opt.text = y;
        year.appendChild(opt);
    }
}

function updateDays() {
    const m = parseInt(month.value), y = parseInt(year.value);
    let selectedDay = day.value;
    let daysInMonth = (m && y) ? new Date(y, m, 0).getDate() : 31;
    day.innerHTML = '<option value="">День</option>';
    for (let i = 1; i <= daysInMonth; i++) {
        const opt = document.createElement('option');
        opt.value = opt.text = i < 10 ? '0' + i : i;
        day.appendChild(opt);
    }
    if (selectedDay && selectedDay <= daysInMonth) {
        day.value = selectedDay;
    }
}

function validateDob() {
    const d = day.value, m = month.value, y = year.value;
    const valid = d && m && y;
    dobInput.value = valid ? `${d}/${m}/${y}` : '';
    dobError.classList.toggle('d-none', valid);
    return valid;
}

function validateSelect(sel, error) {
    const valid = sel.value !== '';
    error.classList.toggle('d-none', valid);
    sel.classList.toggle('is-invalid', !valid);
    return valid;
}

function validateField(id, validator) {
    const el = document.getElementById(id);
    const val = el.value.trim();
    const valid = validator(val);
    el.classList.toggle('is-invalid', !valid);
    if (el.nextElementSibling && id !== 'email') {
        el.nextElementSibling.classList.toggle('d-none', valid);
    }
    return valid;
}

// Проверка уникальности email
async function checkEmailUnique() {
    const email = emailInput.value.trim();

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return false;
    }

    try {
        const response = await fetch('check_email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        });

        const data = await response.json();

        if (data.exists) {
            emailError.textContent = 'эта почта уже используется';
            emailError.classList.remove('d-none');
            emailFormatError.classList.add('d-none');
            emailInput.classList.add('is-invalid');
            return false;
        } else {
            emailError.classList.add('d-none');
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailFormatError.classList.add('d-none');
                emailInput.classList.remove('is-invalid');
            }
            return true;
        }
    } catch (error) {
        console.error('Ошибка при проверке email:', error);
        return true;
    }
}

async function validateForm() {
    const validDob = validateDob();
    const validFIO = validateField('FIO', v => v.length > 0);
    const validAddress = validateField('address', v => v.length > 0);
    const validInteres = validateField('interes', v => v.length > 0);
    const validVK = validateField('vk', v => /^https?:\/\/.+$/.test(v));

    // Валидация email формата
    const email = emailInput.value.trim();
    const validEmailFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    emailInput.classList.toggle('is-invalid', !validEmailFormat);
    emailFormatError.classList.toggle('d-none', validEmailFormat);

    const validPassword = validateField('password', v => v.length >= 7);
    const validConfirm = validateField('confirmPassword', v => v === document.getElementById('password').value);
    const validBlood = validateSelect(blood, bloodError);
    const validResus = validateSelect(resus, resusError);
    const validSex = validateSelect(sex, sexError);

    // Проверка уникальности email
    let validEmailUnique = true;
    if (validEmailFormat) {
        validEmailUnique = await checkEmailUnique();
    }

    submitBtn.disabled = !(validDob && validFIO && validAddress && validInteres &&
                           validVK && validEmailFormat && validPassword && validConfirm &&
                           validBlood && validResus && validSex && validEmailUnique);
}

// Инициализация
populateMonths();
populateYears();
updateDays();

// Слушатели изменений
month.addEventListener('change', () => { updateDays(); validateForm(); });
year.addEventListener('change', () => { updateDays(); validateForm(); });
day.addEventListener('change', validateForm);
blood.addEventListener('change', validateForm);
resus.addEventListener('change', validateForm);
sex.addEventListener('change', validateForm);

// Слушатели для полей ввода
document.getElementById('regForm').addEventListener('input', validateForm);

// Специальный обработчик для email
emailInput.addEventListener('blur', async function() {
    await checkEmailUnique();
    validateForm();
});

emailInput.addEventListener('input', function() {
    // Скрываем ошибку уникальности при изменении email
    emailError.classList.add('d-none');
    validateForm();
});

// Запускаем валидацию при загрузке
validateForm();
