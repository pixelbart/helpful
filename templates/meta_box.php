<table class="form-table meta_box">
    <tbody>    
        <tr>
            <th style="width:20%">
                <label><?php _e( 'Pro', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td><?php echo $pros; ?> <?php printf("(%s%%)", $pro_percent); ?></td>
        </tr>
        <tr>
            <th style="width:20%">
                <label><?php _e( 'Contra', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td><?php echo $cons; ?> <?php printf("(%s%%)", $contra_percent); ?></td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helfpul_remove_single"><?php _e( 'Reset Post', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <input type="checkbox" name="helfpul_remove_single" id="helfpul_remove_single" value="1">
                <label for="helfpul_remove_single"><span class="description"><?php _ex( 'Select to reset the entries of Helpful for this post.', 'checkbox label', 'helpful'); ?></span></label>
            </td>
        </tr>
    </tbody>
</table>