<assets>
    <?php foreach (assetic_stylesheets(
        ['foo.css', 'bar.css'],
        ['?foo', 'bar'],
        ['name' => 'test123', 'output' => 'css/packed.css', 'debug' => true]) as $url): ?>
        <asset url="<?php echo $url ?>" />
    <?php endforeach; ?>
</assets>
