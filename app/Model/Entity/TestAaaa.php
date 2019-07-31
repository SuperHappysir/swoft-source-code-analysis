<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 
 * Class TestAaaa
 *
 * @since 2.0
 *
 * @Entity(table="test_aaaa")
 */
class TestAaaa extends Model
{
    /**
     * ??
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * ??
     *
     * @Column()
     *
     * @var string
     */
    private $name;

    /**
     * 2
     *
     * @Column()
     *
     * @var int
     */
    private $num;


    /**
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param int $num
     *
     * @return void
     */
    public function setNum(int $num): void
    {
        $this->num = $num;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getNum(): ?int
    {
        return $this->num;
    }

}
