using System;
//DISEÑAR UN ALGORITMO QUE SIMULE UNA CARRERA DE AUTOS DONDE CADA AUTO TIENE UNA ACELERACION Y UNA VELOCIDAD MAXIMA.
//CALCULAR QUIEN GANA UNA CARRERA DE 4KM.
namespace Carrera_de_autos
{
    internal class Program
    {
        struct auto
        {
            public double a;
            public double v;
            public double d;
            public double vm;
        }

        static void calcular(ref auto c, int t)
        {
            c.v = c.a * t; // velocidad = aceleracion x tiempo
            if (c.v > c.vm) c.v = c.vm; // si la velocidad es mayor a la velocidad maxima la velocidad maxima pasa a ser la velocidad
            c.d = c.v * t; // distancia = velocidad x tiempo
        }

        static void Main(string[] args)
        {
            auto[] piloto = new auto[2];
            piloto[0].a = 1;
            piloto[0].v = 0;
            piloto[0].d = 0;
            piloto[0].vm = 280;

            piloto[1].a = 2;
            piloto[1].v = 0;
            piloto[1].d = 0;
            piloto[1].vm = 300;
            int t = 0;

            do
            {
                calcular(ref piloto[0], t);
                calcular(ref piloto[1], t);
                t++;
            }
            while (piloto[0].d < 4000 && piloto[1].d < 4000);

            if (piloto[0].d >= 4000 && piloto[1].d >= 4000)
                Console.WriteLine("Empate");

            else if (piloto[0].d >= 4000)
                Console.WriteLine("El ganador es el piloto 1");

            else Console.WriteLine("El ganador es el piloto 2");


        }
    }
}
