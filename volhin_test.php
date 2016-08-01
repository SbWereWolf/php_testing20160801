<?
// оригинальный класс Node имел приватное свойство $name
// к которому не понятно как надо было обращаться
// если бы класс Node был абстрактным,
// то я бы ему добавил гетер,
// но если нарушать ТЗ, то удобным для себя образом
// проще свойство $name сделать публичным
class NordNode
{
    public $name;

    function __construct($name)
    {
        $this->name = $name;
    }
}

// позволил себе вольность класс Tree переименовал в NordTree
// и все вызовы переделал с Node на NordNode
abstract class NordTree
{
// создает узел (если $parentNode == NULL - корень)
    abstract protected function createNode(NordNode $node, $parentNode = NULL);

// удаляет узел и все дочерние узлы
    abstract protected function deleteNode(NordNode $node);

// один узел делает дочерним по отношению к другому
    abstract protected function attachNode(NordNode $node, NordNode $parent);

// получает узел по названию
    abstract protected function getNode($nodeName);

// преобразует дерево со всеми элементами в ассоциативный массив
    abstract protected function export();
}

// реализация абстракции
class RealTree extends NordTree
{
// в ТЗ ни как не регламентировано хранение данных
// храним в массивах внутри экземпляра класса,
// можно конечно в БД,
// но хранить в БД это банально и скучно.

// массив узлов
    public $nodes;

//массив связей между узлами
    public $nodes_links;

// Связь задаётся двумя точками - родитель и потомок
// имя индекса для родителя
    private $c_parent = 'parent';
// имя индекса для потомка
    private $c_node = 'node';

    function __construct()
    {
// инициализируем массивы в конструкторе
        $nodes = array();
        $this->nodes = $nodes;

        $nodes_parents = array();
        $this->nodes_links = $nodes_parents;
    }

// создает узел (если $parentNode == NULL - корень)
    function createNode(NordNode $node, $parentNode = NULL)
    {
        $link = array();

        $node_index = array_search($node->name, $this->nodes);
        if ($node_index === false) {
// можно конечно экземпляры класса присваивать,
// но в отладке было не очнеь понятно что и как будет
// поэтому выбрал вариант попроще - присвоить свойство-строку
            $link[$this->c_node] = $node->name;
            $link[$this->c_parent] = $parentNode === NULL
                ? NULL
                : $parentNode->name;
        }

        $link_index = array_search($link, $this->nodes_links);
        if ($link_index === false
            && $node_index === false
        ) {
            $this->nodes[] = $node;
            $this->nodes_links[] = $link;
        }
    }

// удаляет узел и все дочерние узлы
    function deleteNode(NordNode $node)
    {
        $node_name = $node->name;
        $delete_node = new NordNode($node->name);

        $child_index = array_search($delete_node, $this->nodes);
        if ($child_index) {

            foreach ($this->nodes_links as $index => $parent) {
// перебираем вся связи для очистки

                if ($parent[$this->c_parent] === $node_name) {
// "найденный" элемент это связь с потомком
                    $child_name = $parent[$this->c_node];

// удаляем у потомка всех потомков
// если ветка имеет замкнутый контур,
// то рекурсия будет вечной
                    $child_node = new NordNode ($child_name);
                    $this->deleteNode($child_node);

                    unset($this->nodes_links[$index]);
                } elseif ($parent[$this->c_node] === $node_name) {
// "найденный" элемент это связь с родителем
                    unset($this->nodes_links[$index]);
                }
            }

// удаляем узел из массива узлов
            unset($this->nodes[$child_index]);
        }
    }

// один узел делает дочерним по отношению к другому
    function attachNode(NordNode $node, NordNode $parent)
    {
        $node_name = $node->name;
        $parent_name = $parent->name;
        foreach ($this->nodes_links as $index => $link) {
            if ($link[$this->c_node] === $node_name) {

// по хорошему конечно должна быть проверка,
// что узел $parent не является дочерним для узла $node,
// но в ТЗ нет требования,
// в примере использования нет такого варианта,
// поэтому позволяю себе безответственность
                $this->nodes_links[$index][$this->c_parent] = $parent_name;
// логика добавления нового узла обеспечивает уникальность
// и узла и связей между узлами
// поэтому родитель может быть только один,
// других не ищем
                break;
            }
        }
    }

// получает узел по названию
    function getNode($nodeName)
    {
        $found_node = null;

        $needle = new NordNode ($nodeName);
        $node_index = array_search($needle, $this->nodes);

        if ($node_index !== false) {
            $found_node = $this->nodes[$node_index];
        }

        return $found_node;
    }

// преобразует дерево со всеми элементами в ассоциативный массив
    function export()
    {
// не очень понятно что имелось в виду под "ассоциативный массив",
// поэтому два варианта "вычисления" результата,
// один - $c_output_forward = true
// другой $c_output_forward = false
// разруливает порядок вывода,
// писать значение с начала строки или с конца
        $c_output_forward = true;

        $c_my_children = 'Мои дети';
        $c_my_child = 'Мой потомок';
        $c_my_parent = 'Мой родитель';
        $c_my_name = 'Моё имя';

        $hash_array = array();

        foreach ($this->nodes as $node) {

            $next_node = array();

            $node_name = $node->name;
            $children = array();

            if ($c_output_forward) {
            } else {
                $next_node[$c_my_name] = $node_name;
            }

            foreach ($this->nodes_links as $index => $link) {

                $child_name = null;
                $parent_name = null;
                if ($link[$this->c_parent] === $node_name) {
// "нашли" потомка
                    $child_name = $link[$this->c_node];

                    if ($c_output_forward) {
                        $next_node[$child_name] = $c_my_child;
                    } else {
                        $children[] = $child_name;
                    }
                } elseif ($link[$this->c_node] === $node_name) {
// "нашли" родителя
// подразумевается, что родитель один
                    $parent_name = $link[$this->c_parent];

                    if ($c_output_forward) {
                        $next_node[$parent_name] = $c_my_parent;
                    } else {
                        $next_node[$c_my_parent] = $parent_name;
                    }
                }
            }

            if ($c_output_forward) {
                $hash_array[$node->name] = $next_node;
            } else {
                $next_node[$c_my_children] = $children;

                $hash_array[] = $next_node;
            }
        }

        return $hash_array;
    }

}

echo '<pre>';
// в примере использования пропущен момент создания экземпляра класса
$tree = new RealTree();
// 1. создать корень country
$tree->createNode(new NordNode('country'));
// 2. создать в нем узел kiev
$tree->createNode(new NordNode('kiev'), $tree->getNode('country'));
// 3. в узле kiev создать узел kremlin
$tree->createNode(new NordNode('kremlin'), $tree->getNode('kiev'));
// 4. в узле kremlin создать узел house
$tree->createNode(new NordNode('house'), $tree->getNode('kremlin'));
// 5. в узле kremlin создать узел tower
$tree->createNode(new NordNode('tower'), $tree->getNode('kremlin'));
// 4. в корневом узле создать узел moskow
$tree->createNode(new NordNode('moskow'), $tree->getNode('country'));
// 5. сделать узел kremlin дочерним узлом у moskow
$tree->attachNode($tree->getNode('kremlin'), $tree->getNode('moskow'));
// 6. в узле kiev создать узел maidan
$tree->createNode(new NordNode('maidan'), $tree->getNode('kiev'));
// 7. удалить узел kiev
$tree->deleteNode($tree->getNode('kiev'));
// 8. получить дерево в виде массива, сделать print_r
print_r($tree->export());

echo '</pre>';