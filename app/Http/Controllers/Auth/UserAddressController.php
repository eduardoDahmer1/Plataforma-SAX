<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserAddressController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureLegacyAddress($request);

        return view('users.addresses', ['addresses' => $request->user()->addresses()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        DB::transaction(function () use ($user, $data): void {
            $makeDefault = (bool) ($data['is_default'] ?? false) || ! $user->addresses()->exists();
            unset($data['is_default']);

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $address = $user->addresses()->create($data + ['is_default' => $makeDefault]);

            if ($makeDefault) {
                $this->syncLegacyAddress($user, $address);
            }
        });

        return back()->with('success', 'Endereço salvo com sucesso.');
    }

    public function makeDefault(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);

        DB::transaction(function () use ($request, $address): void {
            $request->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
            $this->syncLegacyAddress($request->user(), $address);
        });

        return back()->with('success', 'Endereço padrão atualizado.');
    }

    public function update(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);

        $request->merge(['editing_address_id' => $address->id]);
        $data = $this->validated($request);
        $user = $request->user();

        DB::transaction(function () use ($user, $address, $data): void {
            $makeDefault = $address->is_default || (bool) ($data['is_default'] ?? false);
            unset($data['is_default']);

            if ($makeDefault) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($data + ['is_default' => $makeDefault]);

            if ($makeDefault) {
                $this->syncLegacyAddress($user, $address);
            }
        });

        return back()->with('success', 'Endereço atualizado com sucesso.');
    }

    public function destroy(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);
        abort_if($request->user()->addresses()->count() === 1, 422, 'Cadastre outro endereço antes de remover este.');

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault && $next = $request->user()->addresses()->first()) {
            $next->update(['is_default' => true]);
            $this->syncLegacyAddress($request->user(), $next);
        }

        return back()->with('success', 'Endereço removido.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'country' => ['required', 'in:brasil,paraguai'],
            'postal_code' => ['required_if:country,brasil', 'nullable', 'string', 'max:30'],
            'state' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:40'],
            'district' => ['required', 'string', 'max:160'],
            'complement' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function syncLegacyAddress($user, UserAddress $address): void
    {
        $user->update([
            'country' => $address->country,
            'cep' => $address->postal_code,
            'state' => $address->state,
            'city' => $address->city,
            'address' => $address->street,
            'number' => $address->number,
            'district' => $address->district,
            'complement' => $address->complement,
        ]);
    }

    private function ensureLegacyAddress(Request $request): void
    {
        $user = $request->user();

        if ($user->addresses()->exists() || ! filled($user->address) || ! filled($user->number) || ! filled($user->district)) {
            return;
        }

        $address = $user->addresses()->create([
            'label' => 'Endereço principal',
            'country' => in_array(mb_strtolower((string) $user->country), ['paraguai', 'py'], true) ? 'paraguai' : 'brasil',
            'postal_code' => $user->cep,
            'state' => $user->state,
            'city' => $user->city,
            'street' => $user->address,
            'number' => $user->number,
            'district' => $user->district,
            'complement' => $user->complement,
            'is_default' => true,
        ]);

        $this->syncLegacyAddress($user, $address);
    }
}
