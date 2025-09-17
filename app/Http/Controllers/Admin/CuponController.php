<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CuponController extends Controller
{
    public function index()
    {
        $cupons = Cupon::latest()->paginate(10);
        return view('admin.cupon.index', compact('cupons'));
    }

    public function create()
    {
        return view('admin.cupon.create');
    }

    public function store(Request $request)
    {
        try {
            $this->validateCupon($request);
            $data = $this->prepareCuponData($request);

            Log::info('Tentativa de criar cupom', ['data' => $data]);

            Cupon::create($data);

            return redirect()->route('admin.cupons.index')->with('success', 'Cupom criado com sucesso!');
        } catch (ValidationException $e) {
            // Tratamento específico para código duplicado
            if (isset($e->errors()['codigo'])) {
                return back()->withInput()->with('error', 'Este cupom já existe!');
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erro ao criar cupom', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Não foi possível criar o cupom: ' . $e->getMessage());
        }
    }

    public function show(Cupon $cupon)
    {
        return view('admin.cupon.show', compact('cupon'));
    }

    public function edit(Cupon $cupon)
    {
        return view('admin.cupon.edit', compact('cupon'));
    }

    public function update(Request $request, Cupon $cupon)
    {
        try {
            $this->validateCupon($request, $cupon->id);
            $data = $this->prepareCuponData($request);

            Log::info('Tentativa de atualizar cupom', ['id' => $cupon->id, 'data' => $data]);

            $cupon->update($data);

            return redirect()->route('admin.cupons.index')->with('success', 'Cupom atualizado com sucesso!');
        } catch (ValidationException $e) {
            if (isset($e->errors()['codigo'])) {
                return back()->withInput()->with('error', 'Este cupom já existe!');
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erro ao atualizar cupom', [
                'id' => $cupon->id,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Não foi possível atualizar o cupom: ' . $e->getMessage());
        }
    }

    public function destroy(Cupon $cupon)
    {
        $cupon->delete();
        return redirect()->route('admin.cupons.index')->with('success', 'Cupom deletado com sucesso!');
    }

    /**
     * Validação personalizada para cupons
     */
    private function validateCupon(Request $request, $cuponId = null)
    {
        $request->validate([
            'codigo'       => 'required|unique:cupons,codigo,' . $cuponId,
            'data_inicio'  => 'required|date',
            'data_final'   => 'required|date|after_or_equal:data_inicio',
            'tipo'         => 'required|in:percentual,valor_fixo',
            'montante'     => 'required|numeric|min:0',
            'modelo'       => 'nullable|in:categoria,marca,produto',
            'categoria_id' => 'nullable|exists:categories,id',
            'marca_id'     => 'nullable|exists:brands,id',
            'produto_id'   => 'nullable|exists:products,id',
        ]);

        $modelo = $request->input('modelo');
        $categoria = $request->input('categoria_id');
        $marca = $request->input('marca_id');
        $produto = $request->input('produto_id');

        if ($modelo === 'categoria' && !$categoria) {
            abort(422, 'Selecione uma categoria para este cupom.');
        } elseif ($modelo === 'marca' && !$marca) {
            abort(422, 'Selecione uma marca para este cupom.');
        } elseif ($modelo === 'produto' && !$produto) {
            abort(422, 'Selecione um produto para este cupom.');
        }
    }

    /**
     * Prepara os dados do cupom para salvar, limpando os campos que não correspondem ao modelo
     */
    private function prepareCuponData(Request $request)
    {
        $data = $request->only([
            'codigo', 'data_inicio', 'data_final', 'valor_minimo', 'valor_maximo',
            'tipo', 'montante', 'quantidade', 'modelo', 'categoria_id', 'marca_id', 'produto_id'
        ]);

        switch ($data['modelo'] ?? null) {
            case 'categoria':
                $data['marca_id'] = null;
                $data['produto_id'] = null;
                break;
            case 'marca':
                $data['categoria_id'] = null;
                $data['produto_id'] = null;
                break;
            case 'produto':
                $data['categoria_id'] = null;
                $data['marca_id'] = null;
                break;
            default:
                $data['categoria_id'] = null;
                $data['marca_id'] = null;
                $data['produto_id'] = null;
                break;
        }

        return $data;
    }
}
