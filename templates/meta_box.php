<table class="form-table meta_box">
    <tbody>    
        <tr>
            <th style="width:20%">
                <label><?php echo esc_html_x( 'Pro', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td><?php echo $pro; ?> <?php printf("(%s%%)", $pro_percent); ?></td>
        </tr>
        <tr>
            <th style="width:20%">
                <label><?php echo esc_html_x( 'Contra', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td><?php echo $contra; ?> <?php printf("(%s%%)", $contra_percent); ?></td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helfpul_remove_single"><?php echo esc_html_x( 'Reset Post', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <input type="checkbox" name="helfpul_remove_single" id="helfpul_remove_single" value="1">
                <label for="helfpul_remove_single">
                    <span class="description"><?php echo esc_html_x( 'Select to reset the entries of Helpful for this post.', 'checkbox label', 'helpful'); ?></span>
                </label>
            </td>
        </tr>
    </tbody>
</table>