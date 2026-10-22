let currentId = null;

function escapeHtml(value = '') {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function showMessage(text, type = 'danger') {
    apiMessage.innerHTML = `<div class="alert alert-${type} alert-dismissible">${escapeHtml(text)}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button></div>`;
}

async function requestJson(url, options = {}) {
    const res = await fetch(url, options);
    const payload = await res.json();
    if (!res.ok || payload.success === false) {
        const details = payload.fields ? ' ' + Object.values(payload.fields).join(' ') : '';
        throw new Error((payload.message || 'Ошибка запроса.') + details);
    }
    return payload;
}

async function loadCarModels() {
    const payload = await requestJson('api/get_car_models');
    const selects = [add_car_model, edit_car_model];
    selects.forEach(select => {
        select.innerHTML = '';
        payload.data.forEach(m => {
            select.innerHTML += `<option value="${m.id}">${escapeHtml(m.name)}</option>`;
        });
    });
}

// Сегодняшняя дата в формате ГГГГ-ММ-ДД для <input type="date">
function todayDate() {
    const now = new Date();
    return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

// ГГГГ-ММ-ДД -> ДД.ММ.ГГГГ
function formatDate(date) {
    return String(date).split('-').reverse().join('.');
}

// Фото отзыва или кружок с первой буквой имени, если фото нет.
// Уже показанное фото берётся из shownPhotos: элемент переносится в новую строку, а не создаётся заново.
// Иначе при каждом обновлении таблицы Firefox отменяет повторную загрузку картинки из кэша
// и показывает в Network красные запросы NS_BINDING_ABORTED.
function photoNode(path, name, shownPhotos) {
    const src = `catalogue_images/${encodeURIComponent(path)}`;
    if (path && shownPhotos.has(src)) {
        return shownPhotos.get(src);
    }
    const template = document.createElement('template');
    if (path) {
        template.innerHTML = `<img src="${src}" width="48" height="48"
                                   class="rounded-circle object-fit-cover" alt="Фото">`;
    } else {
        const initial = String(name).charAt(0).toUpperCase();
        template.innerHTML = `<span class="d-inline-block bg-body-secondary text-body-secondary rounded-circle
                                     fs-5 fw-semibold font-monospace px-3 py-2">${escapeHtml(initial)}</span>`;
    }
    return template.content.firstElementChild;
}

async function loadReviews() {
    const payload = await requestJson('api/read');
    const shownPhotos = new Map();
    reviews.querySelectorAll('img').forEach(img => shownPhotos.set(img.getAttribute('src'), img));
    reviews.innerHTML = '';
    if (payload.data.length === 0) {
        reviews.innerHTML = `<tr>
            <td colspan="6" class="text-center text-body-secondary py-4">Отзывов пока нет</td>
        </tr>`;
        return;
    }
    payload.data.forEach(r => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td></td>
            <td class="fw-semibold text-nowrap">${escapeHtml(r.name)}</td>
            <td class="small text-warning text-nowrap">${escapeHtml(r.car_model_name || 'Не указана')}</td>
            <td class="text-nowrap">${escapeHtml(formatDate(r.review_date))}</td>
            <td class="small text-body-secondary">${escapeHtml(r.review_text)}</td>
            <td class="text-end">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary btn-edit">Изменить</button>
                    <button class="btn btn-outline-secondary btn-delete">Удалить</button>
                </div>
            </td>`;
        row.cells[0].append(photoNode(r.img_path, r.name, shownPhotos));
        row.querySelector('.btn-edit').addEventListener('click', () => openEdit(r));
        row.querySelector('.btn-delete').addEventListener('click', () => removeReview(r.id));
        reviews.appendChild(row);
    });
}

function openEdit(review) {
    currentId = review.id;
    edit_name.value = review.name;
    edit_review_date.value = review.review_date;
    edit_car_model.value = review.car_model_id;
    edit_text.value = review.review_text;
    edit_photo.value = '';
    new bootstrap.Modal(editModal).show();
}

// Фото загружается отдельным запросом после сохранения отзыва: имя файла строится из его id
async function uploadPhoto(id, input, successText) {
    const file = input.files[0];
    input.value = '';
    try {
        if (file) {
            const form = new FormData();
            form.append('id', id);
            form.append('photo', file);
            await requestJson('api/upload_photo', {method: 'POST', body: form});
        }
        showMessage(successText, 'success');
    } catch (error) {
        showMessage(successText + ' Но фото не загружено: ' + error.message);
    }
    loadReviews();
}

async function createReview() {
    let payload;
    try {
        payload = await requestJson('api/create', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                name: add_name.value,
                review_date: add_review_date.value,
                car_model_id: add_car_model.value,
                review_text: add_text.value
            })
        });
    } catch (error) {
        showMessage(error.message);
        return;
    }
    bootstrap.Modal.getInstance(addModal).hide();
    [add_name, add_text].forEach(field => field.value = '');
    add_review_date.value = todayDate();
    await uploadPhoto(payload.data.id, add_photo, 'Отзыв добавлен.');
}

async function updateReview() {
    try {
        await requestJson('api/update', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id: currentId,
                name: edit_name.value,
                review_date: edit_review_date.value,
                car_model_id: edit_car_model.value,
                review_text: edit_text.value
            })
        });
    } catch (error) {
        showMessage(error.message);
        return;
    }
    bootstrap.Modal.getInstance(editModal).hide();
    await uploadPhoto(currentId, edit_photo, 'Отзыв обновлён.');
}

async function removeReview(id) {
    if (!confirm('Удалить отзыв?')) return;
    try {
        await requestJson('api/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        });
        showMessage('Отзыв удалён.', 'success');
        loadReviews();
    } catch (error) {
        showMessage(error.message);
    }
}

add_review_date.value = todayDate();

Promise.all([loadCarModels(), loadReviews()]).catch(error => showMessage(error.message));
