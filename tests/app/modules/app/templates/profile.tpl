
<div class="block">
    <h2>Your profile</h2>
    <div class="blockcontent">
        <p>Hello. This is your attributes</p>
        <table>
            <tr>
                <th>Attribute</th>
                <th>Value</th>
            </tr>
            {foreach $attributes as $attr => $val}
                <tr>
                    <td>{$attr|eschtml}</td>
                    <td>{$val|eschtml}</td>
                </tr>
            {/foreach}
        </table>

    </div>
</div>