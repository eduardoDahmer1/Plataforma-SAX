<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Support\CountrySupport;
use App\Services\StoreControlService;

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
        $country = CountrySupport::normalizeForStorage($request->input('country'));
        $request->merge(['country' => $country]);

        if (CountrySupport::usesDhl($country) && ! app(StoreControlService::class)->enabled('geonames')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'country' => 'As localidades internacionais ainda não estão habilitadas. Selecione Brasil ou Paraguai.',
            ]);
        }

        return $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'country' => ['required', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! CountrySupport::isSupported($value)) {
                    $fail('Selecione um país válido.');
                }
            }],
            'postal_code' => [Rule::requiredIf(! CountrySupport::isParaguay($country)), 'nullable', 'string', 'max:30'],
            'state' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:40'],
            'district' => [Rule::requiredIf(! CountrySupport::usesDhl($country)), 'nullable', 'string', 'max:160'],
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
            'country' => CountrySupport::normalizeForStorage($user->country) ?: CountrySupport::BRAZIL,
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
