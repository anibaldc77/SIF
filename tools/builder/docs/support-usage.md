# Uso de Support

```php
use Sif\Support\Collections\ArrayCollection;
use Sif\Support\ValueObjects\Version;

$version = Version::fromString('2.0.0-alpha1');
$ids = (new ArrayCollection(['a', 'b']))->with('c');
```
