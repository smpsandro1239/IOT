<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Company; // Para listar empresas no formulário de criação/edição
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // Para regras de validação mais complexas

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Site::with('company')->orderBy('company_id')->orderBy('name', 'asc');

        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('company_filter')) {
            $query->where('company_id', $request->company_filter);
        }
        if ($request->filled('is_active_filter') && $request->is_active_filter !== 'all') {
            $query->where('is_active', (bool)$request->is_active_filter);
        }

        $sites = $query->withCount('barriers')->paginate(15)->withQueryString(); // Adiciona contagem de barreiras
        $companies_for_filter = Company::orderBy('name')->get();

        return view('admin.sites.index', compact('sites', 'companies_for_filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.sites.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sites')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                }),
            ],
            'address' => 'nullable|string|max:255',
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        Site::create($validatedData);
        return redirect()->route('admin.sites.index')->with('success', 'Site criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.sites.edit', compact('site', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sites')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })->ignore($site->id),
            ],
            'address' => 'nullable|string|max:255',
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        $site->update($validatedData);
        return redirect()->route('admin.sites.index')->with('success', 'Site atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        try {
            // onDelete('cascade') na migration de 'barriers' deve cuidar das barreiras associadas.
            $site->delete();
            return redirect()->route('admin.sites.index')->with('success', 'Site excluído com sucesso!');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Erro de Query ao excluir site ID {$site->id}: " . $e->getMessage());
            return redirect()->route('admin.sites.index')->with('error', 'Erro ao excluir site. Verifique se há entidades associadas (barreiras, etc.) se o onDelete cascade não estiver configurado corretamente ou se houver outras restrições.');
        } catch (\Exception $e) {
            Log::error("Erro geral ao excluir site ID {$site->id}: " . $e->getMessage());
            return redirect()->route('admin.sites.index')->with('error', 'Erro ao excluir site.');
        }
    }
}
