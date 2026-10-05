<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Puntuacion;

class GameController extends Controller
{
    public function index() {
        $mejoresTiempos = Puntuacion::where('estado', 'victory')
                            ->orderBy('tiempo_restante', 'desc')
                            ->take(5)
                            ->get();

        return view('game', compact('mejoresTiempos'));
    }

public function storeResult(Request $request) {
        $request->validate([
            'nivel_alcanzado'  => 'required|integer',
            'estado'           => 'required|string',
            'tiempo_restante'  => 'required|integer',
            'puntuacion_final' => 'required|integer', // <--- Validación de la nueva columna
        ]);

        Puntuacion::create([
            'nivel_alcanzado'  => $request->nivel_alcanzado,
            'estado'           => $request->estado,
            'tiempo_restante'  => $request->tiempo_restante,
            'puntuacion_final' => $request->puntuacion_final, // <--- Guardado en MariaDB
        ]);

        return response()->json(['message' => 'Puntuación y tiempo guardados']);
    }
}