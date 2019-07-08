
<div class="block">
    <h2>Your profile</h2>
    <div class="blockcontent">
        <p>Hello. This is your SAML attributes</p>
        <table>
            <tr>
                <th>Attribute</th>
                <th>Value</th>
            </tr>
            {foreach $attributes as $attr => $val}
                <tr>
                    <td>{$attr|eschtml}</td>
                    <td>
                        {if is_array($val)&&count($val)>1}
                            <ul>
                            {foreach $val as $v}
                                <li>{$v|eschtml}</li>
                            {/foreach}
                            </ul>
                        {else}
                            {if is_array($val)}
                                {$val[0]|eschtml}
                            {else}
                                {$val|eschtml}
                            {/if}
                        {/if}
                        </td>
                </tr>
            {/foreach}
        </table>

    </div>
</div>

<div class="block">
    <h2>Your session data</h2>
    <div class="blockcontent">
        <pre>{$session|eschtml}</pre>
    </div>
</div>