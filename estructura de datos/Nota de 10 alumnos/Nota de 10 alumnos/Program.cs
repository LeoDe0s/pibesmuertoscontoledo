using System;

//CARGAR EL NOMBRE Y LA NOTA DE 10 ALUMNOS
namespace Nota_de_10_alumnos
{
    internal class Program
    {
        struct persona {

            public string nombre;
            public int nota;
    
        }

        static void Main(string[] args)
        {

            persona[] alumnos = new persona[2];
            int cont = 0;
            int i = 0;

            do
            {
                Console.WriteLine($"ingrese el nombre del alumno {i + 1}:");
                alumnos[cont].nombre = Console.ReadLine();

                Console.WriteLine("Ingrese la nota del alumno:");
                while (!int.TryParse(Console.ReadLine(), out alumnos[cont].nota))
                {
                    Console.WriteLine("Error, ingrese una nota");
                }
                i++;
                cont++;
            }
            while (cont < 2);

            Console.WriteLine("notas de los alumnos:");
            for (int j = 0; j < alumnos.Length; j++)
            {
                Console.WriteLine($"Alumno {j + 1}: {alumnos[j].nombre}, Nota: {alumnos[j].nota}");
            }

        }

         
    }
}


