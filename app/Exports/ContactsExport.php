<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    protected $type;
    protected $period;

    public function __construct($type = null, $period = 'all')
    {
        $this->type = $type;
        $this->period = $period;
    }

    public function collection()
    {
        $query = Contact::query();

        if ($this->type && in_array((string) $this->type, ['1', '2', '3'], true)) {
            $query->where('contact_type', $this->type);
        }

        [$from, $to] = match ($this->period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfDay()],
            default => [null, null],
        };

        if ($from) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query->select('name', 'email', 'phone', 'message', 'contact_type', 'created_at')->get();
    }

    public function headings(): array
    {
        return ['Nome', 'Email', 'Telefone', 'Mensagem', 'Tipo de Contato', 'Data'];
    }
}
