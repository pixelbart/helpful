<table class="form-table meta_box">
    <tbody>    
        <tr>
            <th style="width:20%"><label><?php echo esc_html_x( 'Pro', 'meta box label', 'helpful' ); ?></label></th>
            <td><?php echo $pro; ?> <?php printf("(%s%%)", $pro_percent); ?></td>
        </tr>
        <tr>
            <th style="width:20%"><label><?php echo esc_html_x( 'Contra', 'meta box label', 'helpful' ); ?></label></th>
            <td><?php echo $contra; ?> <?php printf("(%s%%)", $contra_percent); ?></td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helfpul_remove_data"><?php echo esc_html_x( 'Reset Post', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <input type="checkbox" name="helfpul_remove_data" id="helfpul_remove_data" value="yes">
                <label for="helfpul_remove_data">
                    <span class="description"><?php echo esc_html_x( 'Select to reset the entries of Helpful for this post.', 'checkbox label', 'helpful'); ?></span>
                </label>
            </td>
        </tr>
    </tbody>
</table>