@props([
    'url',
    'background' => '#303236',
    'color' => '#ffffff',
])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:10px 0 0;">
    <tr>
        <td class="sax-email-action" align="center" style="background:{{ $background }};border-radius:3px;">
            <a href="{{ $url }}"
               style="display:block;padding:13px 18px;color:{{ $color }};text-decoration:none;font-size:11px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
