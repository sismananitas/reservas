<?php

namespace App\Http\Requests;

use App\Reservation;
use App\Suite;
use Illuminate\Support\Facades\DB;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Http\FormRequest;

class AddSuiteToCart extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create.reservation');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'habitacion'       => 'required|numeric|min:1',
            'tarifa'           => 'required|string',
            'adultos'          => 'required|numeric|min:1|max:10',
            'ninios'           => 'required|numeric|max:10',
            'fecha_de_entrada' => 'required|date_format:d/m/Y',
            'fecha_de_salida'  => 'required|date_format:d/m/Y|after:fecha_de_entrada',
        ];
    }

    /**
     * Segunda validación
     * 
     */
    public function withValidator($validator)
    {
        $id     = (int) $this->route('product');
        $tarifa = strtolower($this->input('tarifa'));

        $is_product = Cart::content()
        ->where('id', '=', $id)->count();

        $rates = Suite::find($id)->rates()
        ->where('type', '=', $tarifa)->count();

        $validator->after(function ($validator) use ($is_product, $rates, $id) {
            if ($is_product > 0)
            $validator->errors()->add('cart', 'Habitación ya asignada');

            if ($rates <= 0)
            $validator->errors()->add('rate', 'No se encontró una tarifa para esta habitación');

            $from_format = 'd/m/Y h:i A';
            $date_start  = date_create_from_format($from_format, $this->input('fecha_de_entrada') .' '. $this->input('hora_de_entrada'));
            $date_end    = date_create_from_format($from_format, $this->input('fecha_de_salida') .' '. $this->input('hora_de_salida'));

            $reservations = $this->getReservationsFromDates($date_start, $date_end);
            $this->validateArrayReservations($validator, $reservations, $id);
        });
    }

    /**
     * Obtiene todas las reservaciones que hay entre un rango de fechas
     * 
     */
    public function getReservationsFromDates($date_start, $date_end, $to_format = 'Y-m-d H:i:s')
    {
        $start = $date_start->format($to_format);
        $end = $date_end->format($to_format);

        $sql = "SELECT id FROM reservations
        WHERE (start BETWEEN ? AND ?) OR (end BETWEEN ? AND ?)
        OR (? BETWEEN start AND end) OR (? BETWEEN start AND end)";

        $binds = [
            $start, $end, $start, $end, $start, $end,
        ];

        $reservations = DB::select($sql, $binds);
        return $reservations;
    }

    /** Validamos que la fecha no esté ocupada */
    public function validateArrayReservations($validator, $reservations, int $product_id)
    {
        $res = [];
        foreach ($reservations as $reserv) {
            $reservation = Reservation::where('id', '=', $reserv->id)->first();
    
            foreach ($reservation->details as $detail) {
                $suite_number = $detail->suite->id;
                $res[] = $suite_number;

                if ($suite_number === $product_id)
                $validator->errors()->add('disp', '<br>La habitación ya está ocupada en la fecha solicitada.');
            }
        }
    }
}
