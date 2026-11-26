<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Throwable;

class OperatingSchedule
{
    public const WEEKDAY_START_MINUTES = 7 * 60;  // 07:00
    public const WEEKDAY_END_MINUTES = 20 * 60;   // 20:00
    public const SATURDAY_START_MINUTES = 9 * 60; // 09:00
    public const SATURDAY_END_MINUTES = 13 * 60;  // 13:00

    /**
     * Valida una fecha de calendario (sin hora): no permite días pasados ni domingos.
     */
    public static function validateDate(string $value): ?string
    {
        try {
            $date = Carbon::parse($value)->startOfDay();
        } catch (Throwable) {
            return 'La fecha proporcionada no tiene un formato válido.';
        }

        if ($date->lt(today())) {
            return 'No se pueden seleccionar días pasados. Solo se permite la fecha actual o fechas futuras.';
        }

        if ($date->isSunday()) {
            return 'Los domingos son días inhábiles y no se pueden seleccionar.';
        }

        return null;
    }

    /**
     * Valida una fecha y hora operativa de préstamo/devolución:
     * - No permite días pasados ni horas anteriores al momento actual.
     * - Domingos: inhábil (sin horario disponible).
     * - Lunes a viernes: 07:00 a 20:00 hrs.
     * - Sábados: 09:00 a 13:00 hrs.
     */
    public static function validateDateTime(string $value, bool $requireFuture = true): ?string
    {
        try {
            $dateTime = Carbon::parse($value);
        } catch (Throwable) {
            return 'La fecha y hora proporcionada no tiene un formato válido.';
        }

        if ($dateTime->copy()->startOfDay()->lt(today())) {
            return 'No se pueden seleccionar días pasados. Solo se permite la fecha actual o fechas futuras.';
        }

        if ($dateTime->isSunday()) {
            return 'Los domingos son días inhábiles (sin horario disponible). Seleccione un día entre lunes y sábado.';
        }

        $minutes = ($dateTime->hour * 60) + $dateTime->minute;

        if ($dateTime->isSaturday()) {
            if ($minutes < self::SATURDAY_START_MINUTES || $minutes > self::SATURDAY_END_MINUTES) {
                return 'El horario operativo de los sábados es de 09:00 a 13:00 hrs. La hora seleccionada está fuera de rango.';
            }
        } else {
            if ($minutes < self::WEEKDAY_START_MINUTES || $minutes > self::WEEKDAY_END_MINUTES) {
                return 'El horario operativo de lunes a viernes es de 07:00 a 20:00 hrs. La hora seleccionada está fuera de rango.';
            }
        }

        if ($requireFuture && $dateTime->lte(now())) {
            return 'La fecha y hora seleccionada debe ser posterior al momento actual.';
        }

        return null;
    }

    /**
     * Devuelve la siguiente fecha hábil (si cae en domingo, avanza al lunes).
     */
    public static function nextValidDate(?CarbonInterface $reference = null): Carbon
    {
        $date = ($reference ? Carbon::instance($reference) : now())->copy()->startOfDay();

        if ($date->lt(today())) {
            $date = today();
        }

        if ($date->isSunday()) {
            $date->addDay();
        }

        return $date;
    }

    /**
     * Devuelve una fecha y hora por defecto válida dentro del horario operativo.
     */
    public static function defaultReturnDateTime(?CarbonInterface $reference = null): Carbon
    {
        $base = ($reference ? Carbon::instance($reference) : now())->copy()->addDays(2);

        if ($base->isSunday()) {
            $base->addDay();
        }

        $minutes = ($base->hour * 60) + $base->minute;

        if ($base->isSaturday()) {
            if ($minutes < self::SATURDAY_START_MINUTES || $minutes > self::SATURDAY_END_MINUTES) {
                $base->setTime(10, 0, 0);
            }
        } else {
            if ($minutes < self::WEEKDAY_START_MINUTES || $minutes > self::WEEKDAY_END_MINUTES) {
                $base->setTime(10, 0, 0);
            }
        }

        $base->setSecond(0);

        if ($base->lte(now())) {
            $base = now()->copy()->addDay()->setTime(10, 0, 0);
            if ($base->isSunday()) {
                $base->addDay();
            }
        }

        return $base;
    }
}
