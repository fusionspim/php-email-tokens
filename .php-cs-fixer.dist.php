<?php
$config = FusionsPim\PhpCsFixer\Factory::fromDefaults([
    'ordered_class_elements' => true, // Order methods too (our default is to leave methods alone)
]);

return $config->setFinder(
    $config->getFinder()
        ->notName('rector.php')
);
