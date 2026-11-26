@props([
    'name',
    'id' => null,
    'mode' => 'date',
    'value' => '',
    'required' => false,
    'minDateField' => null,
    'placeholder' => 'Seleccionar fecha',
])

@php
    $pickerId = $id ?: 'schedule_' . \Illuminate\Support\Str::slug($name, '_') . '_' . \Illuminate\Support\Str::random(6);
    $initialValue = old($name, $value ?? '');
    if ($initialValue instanceof \Carbon\CarbonInterface) {
        $initialValue = $mode === 'datetime'
            ? $initialValue->format('Y-m-d\TH:i')
            : $initialValue->format('Y-m-d');
    }
@endphp

<div
    class="space-y-1.5"
    data-schedule-picker
    data-picker-id="{{ $pickerId }}"
    data-mode="{{ $mode }}"
    data-required="{{ $required ? 'true' : 'false' }}"
    @if ($minDateField) data-min-date-field="{{ $minDateField }}" @endif
>
    <input
        type="hidden"
        id="{{ $pickerId }}"
        name="{{ $name }}"
        value="{{ $initialValue }}"
        data-schedule-value
    />

    <div class="flex flex-wrap items-center gap-2">
        <button
            type="button"
            data-schedule-trigger
            aria-expanded="false"
            aria-controls="{{ $pickerId }}_calendar"
            class="neu-inset flex-1 min-w-[170px] px-3 py-2 rounded-xl text-xs flex items-center justify-between gap-2 text-left cursor-pointer hover:ring-1 hover:ring-secondary transition"
        >
            <span class="flex items-center gap-2 min-w-0">
                <span class="material-symbols-outlined text-base text-secondary shrink-0">calendar_month</span>
                <span data-schedule-display class="truncate font-mono text-xs text-primary dark:text-surface">
                    {{ $initialValue ?: $placeholder }}
                </span>
            </span>
            <span class="material-symbols-outlined text-sm text-outline shrink-0">expand_more</span>
        </button>

        @if (!$required)
            <button
                type="button"
                data-schedule-clear
                title="Limpiar fecha"
                class="{{ $initialValue ? '' : 'hidden' }} neu-btn-secondary px-2.5 py-2 rounded-xl text-xs text-outline hover:text-error cursor-pointer"
            >
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        @endif

        @if ($mode === 'datetime')
            <div class="flex items-center gap-1 neu-inset px-2.5 py-1.5 rounded-xl" data-schedule-time-group>
                <span class="material-symbols-outlined text-sm text-secondary mr-0.5">schedule</span>
                <select
                    data-schedule-hour
                    aria-label="Hora de devolución"
                    class="bg-transparent border-0 p-0 pr-5 font-mono text-xs text-primary dark:text-surface focus:ring-0 cursor-pointer"
                ></select>
                <span class="font-mono text-xs font-bold text-outline">:</span>
                <select
                    data-schedule-minute
                    aria-label="Minuto de devolución"
                    class="bg-transparent border-0 p-0 pr-5 font-mono text-xs text-primary dark:text-surface focus:ring-0 cursor-pointer"
                ></select>
                <span class="font-mono text-[10px] text-outline ml-0.5">hrs</span>
            </div>
        @endif
    </div>

    <div
        id="{{ $pickerId }}_calendar"
        data-schedule-calendar
        class="hidden mt-2 w-full max-w-[310px] rounded-2xl neu-raised p-3 border border-outline-variant/50 dark:bg-primary-container shadow-lg"
        role="dialog"
        aria-label="Selector de calendario"
    >
        <div class="flex items-center justify-between mb-2 pb-2 border-b border-outline-variant/30">
            <button
                type="button"
                data-schedule-prev-month
                class="p-1 rounded-lg hover:bg-surface-container dark:hover:bg-primary text-primary dark:text-surface disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                aria-label="Mes anterior"
            >
                <span class="material-symbols-outlined text-base">chevron_left</span>
            </button>
            <span data-schedule-month-label class="font-headline text-xs font-bold text-primary dark:text-surface capitalize"></span>
            <button
                type="button"
                data-schedule-next-month
                class="p-1 rounded-lg hover:bg-surface-container dark:hover:bg-primary text-primary dark:text-surface cursor-pointer"
                aria-label="Mes siguiente"
            >
                <span class="material-symbols-outlined text-base">chevron_right</span>
            </button>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center font-mono text-[10px] font-bold mb-1">
            <span class="text-outline py-1">Lun</span>
            <span class="text-outline py-1">Mar</span>
            <span class="text-outline py-1">Mié</span>
            <span class="text-outline py-1">Jue</span>
            <span class="text-outline py-1">Vie</span>
            <span class="text-secondary py-1">Sáb</span>
            <span class="text-error py-1" title="Domingo inhábil">Dom</span>
        </div>

        <div data-schedule-days class="grid grid-cols-7 gap-1 text-center"></div>

        <div class="mt-2 pt-2 border-t border-outline-variant/30 flex items-center justify-between text-[10px] font-mono text-outline">
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-error/60 inline-block"></span>
                Domingos inhábiles
            </span>
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-primary-container inline-block"></span>
                Seleccionado
            </span>
        </div>
    </div>

    <p class="font-mono text-[10px] text-outline">
        @if ($mode === 'datetime')
            Horario operativo: Lun–Vie 07:00–20:00 · Sáb 09:00–13:00 · Dom inhábil
        @else
            Solo se permiten la fecha actual o fechas futuras (domingos inhábiles).
        @endif
    </p>

    <p data-schedule-error class="hidden font-body text-[11px] font-semibold text-error"></p>
</div>

@once
<script>
(function () {
    if (window.__sigaSchedulePickerInitialized) return;
    window.__sigaSchedulePickerInitialized = true;

    const MONTH_NAMES = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    const DAY_SHORT = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function parseLocalDate(str) {
        if (!str || typeof str !== 'string') return null;
        const datePart = str.split('T')[0].split(' ')[0];
        const parts = datePart.split('-').map(Number);
        if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
        const d = new Date(parts[0], parts[1] - 1, parts[2], 0, 0, 0, 0);
        if (d.getFullYear() !== parts[0] || d.getMonth() !== parts[1] - 1 || d.getDate() !== parts[2]) return null;
        return d;
    }

    function parseLocalTime(str) {
        if (!str || typeof str !== 'string') return null;
        const sep = str.includes('T') ? 'T' : (str.includes(' ') ? ' ' : null);
        if (!sep) return null;
        const timePart = str.split(sep)[1];
        if (!timePart) return null;
        const parts = timePart.split(':').map(Number);
        if (parts.length < 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) return null;
        return { hour: parts[0], minute: parts[1] };
    }

    function formatYmd(date) {
        return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;
    }

    function formatHumanDate(date) {
        return `${DAY_SHORT[date.getDay()]} ${pad2(date.getDate())}/${pad2(date.getMonth() + 1)}/${date.getFullYear()}`;
    }

    function getTodayMidnight() {
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);
    }

    function isSameDay(a, b) {
        return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    }

    function getOperatingBounds(dayOfWeek) {
        if (dayOfWeek === 0) return null; // Domingo inhábil
        if (dayOfWeek === 6) return { startHour: 9, endHour: 13 }; // Sábado 09:00-13:00
        return { startHour: 7, endHour: 20 }; // Lun-Vie 07:00-20:00
    }

    function isDayClosedForToday(date, mode) {
        if (mode !== 'datetime') return false;
        const today = getTodayMidnight();
        if (!isSameDay(date, today)) return false;
        const bounds = getOperatingBounds(date.getDay());
        if (!bounds) return true;
        const now = new Date();
        const nowMinutes = now.getHours() * 60 + now.getMinutes();
        return nowMinutes >= bounds.endHour * 60;
    }

    function validateScheduleValue(valueStr, mode, required, minDateObj) {
        if (!valueStr) {
            return required ? 'Debe seleccionar una fecha válida dentro del horario operativo.' : null;
        }
        const date = parseLocalDate(valueStr);
        if (!date) {
            return 'La fecha seleccionada no es válida.';
        }
        const today = getTodayMidnight();
        if (date < today) {
            return 'No se pueden seleccionar días pasados. Solo se permite la fecha actual o fechas futuras.';
        }
        if (minDateObj && date < minDateObj) {
            return 'La fecha seleccionada no puede ser anterior a la fecha inicial.';
        }
        if (date.getDay() === 0) {
            return 'Los domingos son días inhábiles (sin servicio). Seleccione entre lunes y sábado.';
        }

        if (mode === 'datetime') {
            const time = parseLocalTime(valueStr);
            if (!time) {
                return 'Debe seleccionar una hora válida dentro del horario operativo.';
            }
            const bounds = getOperatingBounds(date.getDay());
            const totalMinutes = time.hour * 60 + time.minute;
            if (totalMinutes < bounds.startHour * 60 || totalMinutes > bounds.endHour * 60) {
                if (date.getDay() === 6) {
                    return 'El horario operativo de los sábados es de 09:00 a 13:00 hrs.';
                }
                return 'El horario operativo de lunes a viernes es de 07:00 a 20:00 hrs.';
            }
            const candidate = new Date(date.getFullYear(), date.getMonth(), date.getDate(), time.hour, time.minute, 0, 0);
            if (candidate <= new Date()) {
                return 'La fecha y hora seleccionada debe ser posterior al momento actual.';
            }
        }
        return null;
    }

    function initPicker(root) {
        if (root.dataset.scheduleReady === 'true') return;
        root.dataset.scheduleReady = 'true';

        const mode = root.dataset.mode || 'date';
        const isRequired = root.dataset.required === 'true';
        const minDateField = root.dataset.minDateField || null;

        const hiddenInput = root.querySelector('[data-schedule-value]');
        const triggerBtn = root.querySelector('[data-schedule-trigger]');
        const displayEl = root.querySelector('[data-schedule-display]');
        const clearBtn = root.querySelector('[data-schedule-clear]');
        const calendarEl = root.querySelector('[data-schedule-calendar]');
        const prevMonthBtn = root.querySelector('[data-schedule-prev-month]');
        const nextMonthBtn = root.querySelector('[data-schedule-next-month]');
        const monthLabelEl = root.querySelector('[data-schedule-month-label]');
        const daysGridEl = root.querySelector('[data-schedule-days]');
        const hourSelect = root.querySelector('[data-schedule-hour]');
        const minuteSelect = root.querySelector('[data-schedule-minute]');
        const errorEl = root.querySelector('[data-schedule-error]');

        let selectedDate = parseLocalDate(hiddenInput.value);
        let selectedTime = parseLocalTime(hiddenInput.value) || { hour: 10, minute: 0 };

        // Si el valor inicial es domingo o pasado (por ejemplo, si el usuario abrió en domingo), ajustarlo al próximo día hábil si es requerido
        const today = getTodayMidnight();
        if (selectedDate && (selectedDate < today || selectedDate.getDay() === 0 || isDayClosedForToday(selectedDate, mode))) {
            const candidate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            while (candidate.getDay() === 0 || isDayClosedForToday(candidate, mode)) {
                candidate.setDate(candidate.getDate() + 1);
            }
            selectedDate = candidate;
        }

        const initialView = selectedDate || today;
        let viewYear = initialView.getFullYear();
        let viewMonth = initialView.getMonth();

        function getMinConstraintDate() {
            if (!minDateField) return null;
            const form = root.closest('form');
            const other = form ? form.querySelector(`[name="${minDateField}"]`) : document.querySelector(`[name="${minDateField}"]`);
            return other ? parseLocalDate(other.value) : null;
        }

        function showError(msg) {
            if (!errorEl) return;
            if (msg) {
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
            } else {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }
        }

        function populateTimeOptions() {
            if (mode !== 'datetime' || !hourSelect || !minuteSelect) return;

            const activeDate = selectedDate || today;
            const bounds = getOperatingBounds(activeDate.getDay()) || { startHour: 7, endHour: 20 };
            const isToday = isSameDay(activeDate, getTodayMidnight());
            const now = new Date();
            const curH = now.getHours();
            const curM = now.getMinutes();

            hourSelect.innerHTML = '';
            let firstValidHour = null;

            for (let h = bounds.startHour; h <= bounds.endHour; h++) {
                const opt = document.createElement('option');
                opt.value = pad2(h);
                opt.textContent = pad2(h);

                let disabled = false;
                if (isToday) {
                    if (h < curH) disabled = true;
                    if (h === curH && (h === bounds.endHour || curM >= 59)) disabled = true;
                }
                opt.disabled = disabled;
                if (!disabled && firstValidHour === null) firstValidHour = h;
                hourSelect.appendChild(opt);
            }

            // Elegir hora válida
            let chosenHour = selectedTime.hour;
            if (chosenHour < bounds.startHour || chosenHour > bounds.endHour || hourSelect.querySelector(`option[value="${pad2(chosenHour)}"]`)?.disabled) {
                chosenHour = firstValidHour !== null ? firstValidHour : bounds.startHour;
            }
            selectedTime.hour = chosenHour;
            hourSelect.value = pad2(chosenHour);

            // Poblar minutos
            minuteSelect.innerHTML = '';
            let firstValidMinute = null;
            for (let m = 0; m < 60; m++) {
                const opt = document.createElement('option');
                opt.value = pad2(m);
                opt.textContent = pad2(m);

                let disabled = false;
                if (chosenHour === bounds.endHour && m > 0) {
                    disabled = true; // En hora de cierre (20:00 o 13:00), solo :00
                }
                if (isToday && chosenHour === curH && m <= curM) {
                    disabled = true;
                }
                opt.disabled = disabled;
                if (!disabled && firstValidMinute === null) firstValidMinute = m;
                minuteSelect.appendChild(opt);
            }

            let chosenMinute = selectedTime.minute;
            if (minuteSelect.querySelector(`option[value="${pad2(chosenMinute)}"]`)?.disabled) {
                chosenMinute = firstValidMinute !== null ? firstValidMinute : 0;
            }
            selectedTime.minute = chosenMinute;
            minuteSelect.value = pad2(chosenMinute);
        }

        function syncHiddenAndDisplay() {
            if (!selectedDate) {
                hiddenInput.value = '';
                displayEl.textContent = triggerBtn.dataset.placeholder || 'Seleccionar fecha';
                if (clearBtn) clearBtn.classList.add('hidden');
                return;
            }

            const ymd = formatYmd(selectedDate);
            if (mode === 'datetime') {
                populateTimeOptions();
                hiddenInput.value = `${ymd}T${pad2(selectedTime.hour)}:${pad2(selectedTime.minute)}`;
            } else {
                hiddenInput.value = ymd;
            }

            displayEl.textContent = formatHumanDate(selectedDate);
            if (clearBtn) clearBtn.classList.remove('hidden');

            const err = validateScheduleValue(hiddenInput.value, mode, isRequired, getMinConstraintDate());
            showError(err);

            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function renderCalendar() {
            const todayDate = getTodayMidnight();
            const minConstraint = getMinConstraintDate();
            const effectiveMin = (minConstraint && minConstraint > todayDate) ? minConstraint : todayDate;

            monthLabelEl.textContent = `${MONTH_NAMES[viewMonth]} ${viewYear}`;
            prevMonthBtn.disabled = (viewYear < todayDate.getFullYear()) || (viewYear === todayDate.getFullYear() && viewMonth <= todayDate.getMonth());

            daysGridEl.innerHTML = '';

            const firstOfMonth = new Date(viewYear, viewMonth, 1);
            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            // Lunes = 0, ..., Domingo = 6
            const startOffset = (firstOfMonth.getDay() + 6) % 7;

            for (let i = 0; i < startOffset; i++) {
                const spacer = document.createElement('span');
                spacer.className = 'h-8';
                daysGridEl.appendChild(spacer);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const cellDate = new Date(viewYear, viewMonth, day, 0, 0, 0, 0);
                const isSunday = cellDate.getDay() === 0;
                const isPast = cellDate < effectiveMin;
                const isClosedToday = isDayClosedForToday(cellDate, mode);
                const isDisabled = isSunday || isPast || isClosedToday;
                const isSelected = isSameDay(cellDate, selectedDate);
                const isTodayCell = isSameDay(cellDate, todayDate);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = String(day);
                btn.dataset.date = formatYmd(cellDate);

                if (isDisabled) {
                    btn.disabled = true;
                    btn.setAttribute('aria-disabled', 'true');
                    if (isSunday) {
                        btn.title = 'Domingo: día inhábil';
                        btn.className = 'h-8 rounded-lg font-mono text-[11px] bg-error-container/35 text-error/60 cursor-not-allowed';
                    } else if (isClosedToday) {
                        btn.title = 'Horario operativo de hoy finalizado';
                        btn.className = 'h-8 rounded-lg font-mono text-[11px] opacity-40 text-outline cursor-not-allowed';
                    } else {
                        btn.title = 'Fecha no disponible';
                        btn.className = 'h-8 rounded-lg font-mono text-[11px] opacity-35 line-through text-outline cursor-not-allowed';
                    }
                } else {
                    btn.className = isSelected
                        ? 'h-8 rounded-lg font-mono text-[11px] font-bold bg-primary-container text-white shadow-xs cursor-pointer'
                        : `h-8 rounded-lg font-mono text-[11px] hover:bg-secondary-container/30 text-primary dark:text-surface cursor-pointer transition ${isTodayCell ? 'ring-1 ring-secondary font-bold' : ''}`;

                    btn.addEventListener('click', () => {
                        selectedDate = cellDate;
                        syncHiddenAndDisplay();
                        renderCalendar();
                        calendarEl.classList.add('hidden');
                        triggerBtn.setAttribute('aria-expanded', 'false');
                    });
                }

                daysGridEl.appendChild(btn);
            }
        }

        triggerBtn.dataset.placeholder = triggerBtn.querySelector('[data-schedule-display]')?.textContent?.trim() || 'Seleccionar fecha';

        triggerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = calendarEl.classList.contains('hidden');
            // Cerrar otros calendarios abiertos
            document.querySelectorAll('[data-schedule-calendar]').forEach((cal) => {
                if (cal !== calendarEl) {
                    cal.classList.add('hidden');
                    cal.closest('[data-schedule-picker]')?.querySelector('[data-schedule-trigger]')?.setAttribute('aria-expanded', 'false');
                }
            });
            if (willOpen) {
                renderCalendar();
                calendarEl.classList.remove('hidden');
                triggerBtn.setAttribute('aria-expanded', 'true');
            } else {
                calendarEl.classList.add('hidden');
                triggerBtn.setAttribute('aria-expanded', 'false');
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                selectedDate = null;
                syncHiddenAndDisplay();
                showError(null);
            });
        }

        prevMonthBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            renderCalendar();
        });

        nextMonthBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            renderCalendar();
        });

        if (hourSelect) {
            hourSelect.addEventListener('change', () => {
                selectedTime.hour = parseInt(hourSelect.value, 10);
                syncHiddenAndDisplay();
            });
        }

        if (minuteSelect) {
            minuteSelect.addEventListener('change', () => {
                selectedTime.minute = parseInt(minuteSelect.value, 10);
                syncHiddenAndDisplay();
            });
        }

        if (minDateField) {
            const form = root.closest('form');
            const other = form ? form.querySelector(`[name="${minDateField}"]`) : document.querySelector(`[name="${minDateField}"]`);
            if (other) {
                other.addEventListener('change', () => {
                    const minD = parseLocalDate(other.value);
                    if (selectedDate && minD && selectedDate < minD) {
                        selectedDate = minD;
                        viewYear = minD.getFullYear();
                        viewMonth = minD.getMonth();
                        syncHiddenAndDisplay();
                    }
                    renderCalendar();
                });
            }
        }

        const parentForm = root.closest('form');
        if (parentForm && parentForm.dataset.scheduleSubmitBound !== 'true') {
            parentForm.dataset.scheduleSubmitBound = 'true';
            parentForm.addEventListener('submit', (event) => {
                let firstErrorEl = null;
                parentForm.querySelectorAll('[data-schedule-picker]').forEach((pickerRoot) => {
                    const input = pickerRoot.querySelector('[data-schedule-value]');
                    const pMode = pickerRoot.dataset.mode || 'date';
                    const pReq = pickerRoot.dataset.required === 'true';
                    const pMinField = pickerRoot.dataset.minDateField || null;
                    let minD = null;
                    if (pMinField) {
                        const other = parentForm.querySelector(`[name="${pMinField}"]`);
                        minD = other ? parseLocalDate(other.value) : null;
                    }
                    const err = validateScheduleValue(input.value, pMode, pReq, minD);
                    const errNode = pickerRoot.querySelector('[data-schedule-error]');
                    if (err) {
                        event.preventDefault();
                        if (errNode) {
                            errNode.textContent = err;
                            errNode.classList.remove('hidden');
                        }
                        if (!firstErrorEl) firstErrorEl = pickerRoot;
                    } else if (errNode) {
                        errNode.textContent = '';
                        errNode.classList.add('hidden');
                    }
                });
                if (firstErrorEl) {
                    firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        syncHiddenAndDisplay();
    }

    function initAllPickers() {
        document.querySelectorAll('[data-schedule-picker]').forEach(initPicker);
    }

    document.addEventListener('click', (e) => {
        if (e.target instanceof Element && !e.target.closest('[data-schedule-picker]')) {
            document.querySelectorAll('[data-schedule-calendar]').forEach((cal) => {
                cal.classList.add('hidden');
                cal.closest('[data-schedule-picker]')?.querySelector('[data-schedule-trigger]')?.setAttribute('aria-expanded', 'false');
            });
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllPickers);
    } else {
        initAllPickers();
    }

    window.sigaInitSchedulePickers = initAllPickers;
})();
</script>
@endonce
