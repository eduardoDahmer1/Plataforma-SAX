@props(['url'])

{{-- Boton negro reutilizable para todos los emails. El texto va como slot. --}}
<table cellpadding="0" cellspacing="0" border="0" align="center" style="margin:1.5rem auto 0 auto;">
    <tr>
        <td style="background-color:#000000;">
            <a href="{{ $url }}"
               style="display:inline-block;padding:0.875rem 2rem;color:#ffffff;text-decoration:none;font-size:0.75rem;font-weight:700;letter-spacing:0.2rem;text-transform:uppercase;">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
