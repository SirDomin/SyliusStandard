<?php

namespace App\Form\Extension;

use Sylius\Bundle\OrderBundle\Form\Type\CartItemType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantChoiceType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantMatchType;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\Range;

class CartItemTypeExtension extends AbstractTypeExtension
{
    private string $message = 'The quantity must be divisible by 10.';

    public function __construct(private readonly int $orderItemQuantityModifierLimit)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('quantity');
        $builder->add('quantity', IntegerType::class, [
            'attr' => ['min' => 10, 'step' => 10],
            'label' => 'sylius.ui.quantity',
            'constraints' => [
                new DivisibleBy(['value'=> 10, 'groups'=> 'sylius']),
                new Range([
                    'min' => 10,
                    'max' => $this->orderItemQuantityModifierLimit,
                    'notInRangeMessage' => 'sylius.cart_item.quantity.not_in_range',
                    'groups' => 'sylius',
                ]),
            ],
        ]);

        if (isset($options['product']) && $options['product']->hasVariants() && !$options['product']->isSimple()) {
            $type =
                Product::VARIANT_SELECTION_CHOICE === $options['product']->getVariantSelectionMethod()
                    ? ProductVariantChoiceType::class
                    : ProductVariantMatchType::class
            ;

            $builder->add('variant', $type, [
                'product' => $options['product'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'product',
            ])
            ->setAllowedTypes('product', ProductInterface::class)
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [CartItemType::class];
    }
}
