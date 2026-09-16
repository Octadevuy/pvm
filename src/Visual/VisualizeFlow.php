<?php

namespace Formapro\Pvm\Visual;

use Formapro\Pvm\Node;
use Formapro\Pvm\Token;
use Formapro\Pvm\Process;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Alom\Graphviz\RawText;
use Formapro\Pvm\Transition;
use Graphp\GraphViz\GraphViz;
use Fhaculty\Graph\Edge\Directed;
use Formapro\Pvm\TokenTransition;
use function Formapro\Values\get_value;

class VisualizeFlow
{

  public function createGraph(Process $process)
  {
    $graph = new Graph();
    $graph->setAttribute('graphviz.graph.rankdir', 'TB');
    $graph->setAttribute('graphviz.graph.ranksep', 1);
//        $graph->setAttribute('graphviz.graph.constraint', false);
//        $graph->setAttribute('graphviz.graph.splines', 'ortho');
    $graph->setAttribute('alom.graphviz', [
      'rankdir' => 'TB',
      'ranksep' => 0.2,
	  'size' => '10,100',
	  'fontname' => 'helvetica',
    ]);

    $startVertex = $this->createStartVertex($graph);
    $endVertex = $this->createEndVertex($graph);

    foreach ($process->getNodes() as $node)
    {
      $this->createVertex($graph, $node);
    }

    //$ended = false; gives error: "Fhaculty\Graph\Exception\UnderflowException is caught. Message Does not contain any edges"

    foreach ($process->getTransitions() as $transition)
    {
      $from = $transition->getFrom();
      $to = $transition->getTo();

      if (false == $from && $to) {
        $this->createStartTransition($graph, $startVertex, $transition);
      }

      if ($from && $to) {
        $this->createMiddleTransition($graph, $transition);
      }

      // if (1 === count($process->getInTransitions($transition->getTo())) && empty($process->getOutTransitions($transition->getTo()))) {
      //   $this->createEndTransition($graph, $endVertex, $transition);
      // } else if (false === $ended && empty($process->getOutTransitions($transition->getTo()))) {
      //   $ended = true;
      //   $this->createEndTransition($graph, $endVertex, $transition);
      // }
	 
	  // Changed commented part to original to avoid error mentioned in line 43
	  if (false === $process->hasOutTransitions($to)) {
                $this->createEndTransition($graph, $endVertex, $transition);
      }
	  
    }

    return $graph;
  }

  /**
   * @param Graph $graph
   * @param Process $process
   * @param Token[] $tokens
   */
  public function applyTokens(Graph $graph, Process $process, array $tokens = [])
  {
    $endVertex = $this->createEndVertex($graph);

    // The graph edges do not change here, index them once instead of scanning
    // all of them for every token transition.
    $edges = $this->indexTransitionEdges($graph);

    foreach ($tokens as $token)
    {
      foreach ($token->getTransitions() as $tokenTransition)
      {

        $hasException = get_value($tokenTransition, 'exception', false);

        $transition = $tokenTransition->getTransition();
        $transitionId = $transition->getId();

        if (false == isset($edges[$transitionId])) {
          throw new \LogicException(sprintf('The edge for transition "%s" could not be found.', $transitionId));
        }

        $edge = $edges[$transitionId];

        $alomEdgeAttributes = $edge->getAttribute('alom.graphviz', []);

        if ($edge->getAttribute('pvm.state') === TokenTransition::STATE_PASSED) {
          continue;
        }

        $transitionColor = $this->guessTransitionColor($tokenTransition);

        $edge->setAttribute('pvm.state', $tokenTransition->getState());
        $edge->setAttribute('graphviz.color', $transitionColor);
        $alomEdgeAttributes['color'] = $transitionColor;

        if ($hasException) {
          $edge->getVertexEnd()->setAttribute('graphviz.color', 'red');

          $vertexEndAlomAttributes = $edge->getVertexEnd()->getAttribute('alom.graphviz', []);
          $vertexEndAlomAttributes['color'] = 'red';

          $edge->getVertexEnd()->setAttribute('alom.graphviz', $vertexEndAlomAttributes);
        }

        $transitionTo = $transition->getTo();

        if (false === $process->hasOutTransitions($transitionTo)) {
          $from = $graph->getVertex($transitionTo->getId());
          $endEdge = $from->getEdgesTo($endVertex)->getEdgeFirst();

          if ($edge->getAttribute('pvm.state') === TokenTransition::STATE_PASSED) {
            $endEdge->setAttribute('pvm.state', $tokenTransition->getState());
            $endEdge->setAttribute('graphviz.color', $transitionColor);

            $endEdgeAlomAttribute = $endEdge->getAttribute('alom.graphviz', []);
            $endEdgeAlomAttribute['color'] = $transitionColor;
            $endEdge->setAttribute('alom.graphviz', $endEdgeAlomAttribute);
          }
        }

        $edge->setAttribute('alom.graphviz', $alomEdgeAttributes);
      }
    }
  }

  public function createImageSrc(Graph $graph)
  {
    return (new GraphViz())->createImageSrc($graph);
  }

  public function display(Graph $graph)
  {
    (new GraphViz())->display($graph);
  }

  private function createVertex(Graph $graph, Node $node)
  {
    $nodeId = $node->getId();
    $label = ($node->getLabel() ?: $nodeId);

    $vertex = $graph->createVertex($nodeId);
    $vertex->setAttribute('graphviz.label', $label);
    $vertex->setAttribute('graphviz.id', $nodeId);

    if (null !== $groupId = $node->getOption('group')) {
      $vertex->setAttribute('alom.graphviz_subgroup', $groupId);
    }

    //$shape = $this->getNodeShape($options);
	
	switch ($node->getOption('type')) {  // original was "switch ($options->getType())"

            case 'gateway':
                $shape = 'diamond';
				$color = 'black';
                break;
            case 'component':
                $shape = 'component';
                $color = 'orange';
                break;
            default:
                $shape = 'box';
				$color = 'black';
        }
	
	if(!$color) { $color = $node->getConfig('visual.color') ?? 'black'; }

    $vertex->setAttribute('graphviz.shape', $shape);

    $tooltip = $node->getConfig('visual.tooltip') ?? $label;

    $vertex->setAttribute('alom.graphviz', [
      'id' => $nodeId,
      'label' => new RawText('"' . $label . '"'),
      'tooltip' => $tooltip,
      'color' => $color,
      'fontsize' => 10,
      'shape' => $shape,
	  'fontname' => 'helvetica',
    ]);

    return $vertex;
  }

  /**
   * @param Options $options
   * @return string
   */
  private function getNodeShape(Options $options): string
  {
    if ($options->getType() === 'gateway') {
      return 'diamond';
    }

    if (!empty($options->getType())) {
      return $options->getType();
    }

    return 'box';
  }

  private function createStartTransition(Graph $graph, Vertex $from, Transition $transition)
  {
    $to = $graph->getVertex($transition->getTo()->getId());
    $transitionId = $transition->getId();
    $transitionName = $transition->getName();

    $edge = $from->createEdgeTo($to);
    $edge->setAttribute('pvm.transition_id', $transitionId);
    $edge->setAttribute('graphviz.id', $transitionId);
    $edge->setAttribute('graphviz.label', $transitionName);

    $edge->setAttribute('alom.graphviz', [
      'label' => $transitionName,
      'id' => $transitionId,
	  'fontname' => 'helvetica',
	  'fontsize' => 10,
    ]);
  }

  private function createEndTransition(Graph $graph, Vertex $to, Transition $transition)
  {
    $from = $graph->getVertex($transition->getTo()->getId());
    $transitionId = $transition->getId();
    $transitionName = $transition->getName();

    if ($from->hasEdgeTo($to)) {
      $edge = $from->getEdgesTo($to)->getEdgeFirst();
    } else {
      $edge = $from->createEdgeTo($to);
    }

    $edge->setAttribute('graphviz.label', $transitionName);
    $edge->setAttribute('graphviz.id', $transitionId);
    $edge->setAttribute('pvm.transition_id', $transitionId);

    $edge->setAttribute('alom.graphviz', [
      'label' => $transitionName,
      'id' => $transitionId,
	  'fontname' => 'helvetica',
	  'fontsize' => 10,
    ]);
  }

  private function createMiddleTransition(Graph $graph, Transition $transition)
  {
    $from = $graph->getVertex($transition->getFrom()->getId());
    $to = $graph->getVertex($transition->getTo()->getId());
    $transitionId = $transition->getId();
    $transitionName = $transition->getName();

    $edge = $from->createEdgeTo($to);
    $edge->setAttribute('pvm.transition_id', $transitionId);
    $edge->setAttribute('graphviz.id', $transitionId);
    $edge->setAttribute(
      'graphviz.label',
      $transitionName
    );

    $edge->setAttribute('alom.graphviz', [
      'id' => $transitionId,
      'label' => $transitionName,
	  'fontname' => 'helvetica',
	  'fontsize' => 10,
    ]);
  }

  /**
   * @param Graph $graph
   *
   * @return Vertex
   */
  private function createStartVertex(Graph $graph)
  {
    if (false == $graph->hasVertex('__start')) {
      $vertex = $graph->createVertex('__start');
      $vertex->setAttribute('graphviz.label', 'Start');
      $vertex->setAttribute('graphviz.color', 'blue');
      $vertex->setAttribute('graphviz.shape', 'circle');

      $vertex->setAttribute('alom.graphviz', [
        'label' => 'Start',
        'color' => 'blue',
        'shape' => 'circle',
		'fontname' => 'helvetica',
		'fontsize' => 10,
      ]);
    }

    return $graph->getVertex('__start');
  }

  /**
   * @param Graph $graph
   *
   * @return Vertex
   */
  private function createEndVertex(Graph $graph)
  {
    if (false == $graph->hasVertex('__end')) {
      $vertex = $graph->createVertex('__end');
      $vertex->setAttribute('graphviz.label', 'End');
      $vertex->setAttribute('graphviz.color', 'red');
      $vertex->setAttribute('graphviz.shape', 'circle');

      $vertex->setAttribute('alom.graphviz', [
        'label' => 'End',
        'color' => 'red',
        'shape' => 'circle',
		'fontname' => 'helvetica',
		'fontsize' => 10,
      ]);
    }

    return $graph->getVertex('__end');
  }

  private function guessTransitionColor(TokenTransition $transition): string
  {
    switch ($transition->getState()) {
      case TokenTransition::STATE_INTERRUPTED:
        $transitionColor = 'red';
        break;
      case TokenTransition::STATE_PASSED:
        $transitionColor = 'blue';
        break;
      case TokenTransition::STATE_WAITING:
        $transitionColor = 'orange';
        break;
      default:
        $transitionColor = 'black';
    }

    return $transitionColor;
  }

  /**
   * @return Directed[] Edges indexed by their transition id, the first one wins.
   */
  private function indexTransitionEdges(Graph $graph): array
  {
    $edges = [];

    foreach ($graph->getEdges() as $edge) {
      /** @var Directed $edge */

      $transitionId = $edge->getAttribute('pvm.transition_id');

      if (null !== $transitionId && false == isset($edges[$transitionId])) {
        $edges[$transitionId] = $edge;
      }
    }

    return $edges;
  }
}
