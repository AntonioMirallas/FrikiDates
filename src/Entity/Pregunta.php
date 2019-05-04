<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PreguntaRepository")
 */
class Pregunta
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $texto;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $falsa1;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $falsa2;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $falsa3;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $correcta;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $tipo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexto(): ?string
    {
        return $this->texto;
    }

    public function setTexto(string $texto): self
    {
        $this->texto = $texto;

        return $this;
    }

    public function getFalsa1(): ?string
    {
        return $this->falsa1;
    }

    public function setFalsa1(string $falsa1): self
    {
        $this->falsa1 = $falsa1;

        return $this;
    }

    public function getFalsa2(): ?string
    {
        return $this->falsa2;
    }

    public function setFalsa2(string $falsa2): self
    {
        $this->falsa2 = $falsa2;

        return $this;
    }

    public function getFalsa3(): ?string
    {
        return $this->falsa3;
    }

    public function setFalsa3(string $falsa3): self
    {
        $this->falsa3 = $falsa3;

        return $this;
    }

    public function getCorrecta(): ?string
    {
        return $this->correcta;
    }

    public function setCorrecta(string $correcta): self
    {
        $this->correcta = $correcta;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    // funcion para devolver array de las respuestas shuffleado
    public function devolverRespuestas(): ?array
    {

        $respuestas[0] = $this->falsa1;
        $respuestas[1] = $this->falsa2;
        $respuestas[2] = $this->falsa3;
        $respuestas[3] = $this->correcta;

        shuffle($respuestas);

        return $respuestas;
    }
}
