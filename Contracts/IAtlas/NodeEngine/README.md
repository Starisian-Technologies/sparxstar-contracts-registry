# NodeEngine — Workflow Orchestration

## What Is This?

NodeEngine is the **workflow and process orchestration engine** for SPARXSTAR. It provides:

- **DAG (Directed Acyclic Graph)** definitions — Define complex workflows as node graphs
- **Node types** — Process, IO, decision, and merge nodes
- **State machines** — Track workflow progress and transitions
- **Execution context** — Data flowing through the workflow
- **Result aggregation** — Combine outputs from parallel tasks

NodeEngine lets you define "if this, then that, then that" workflows without coding loops and conditionals.

## Why You Need It

Complex workflows are hard to maintain:
- Transcribe audio → Check for errors → Translate → Store → Notify user
- Should transcribe twice if first one failed
- Handle translate failures gracefully
- Retry certain steps but not others

NodeEngine abstracts this complexity into visual, reusable workflows.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | Workflow definition and orchestration |
| **Use Case** | Multi-step, branching, or parallel tasks |
| **Concepts** | DAGs, nodes, edges, execution state |
| **Part Of** | IAtlas (Data Structures) |

## Core Concepts

### DAG (Directed Acyclic Graph)
A DAG is a way to represent workflows:
- **Nodes** — Steps in the workflow (transcribe, translate, store)
- **Edges** — Connections between steps (depends on)
- **Directed** — Arrows show direction (step A → step B)
- **Acyclic** — No cycles (can't loop back to step A from step B)

```
	┌─────────────┐
	│ Upload      │
	└──────┬──────┘
		   │
	┌──────▼──────────┐
	│ Transcribe      │
	└──────┬──────────┘
		   │
	┌──────▼──────────┬─────────────┐
	│ Branch Check    │             │
	└──────┬──────────┘             │
		   │                        │
	  ┌────┴────┐             ┌─────▼──────┐
	  │ Translate│             │ Fail/Retry │
	  └────┬─────┘             └────────────┘
		   │
	┌──────▼──────────┐
	│ Store Result    │
	└──────┬──────────┘
		   │
	┌──────▼──────────┐
	│ Notify User     │
	└─────────────────┘
```

### Node Types

**Process Node**
- Performs computation
- Takes input, produces output
- May fail (is retryable)
- Example: `transcribe`, `translate`, `store`

**IO Node**
- Receives input from outside or sends output
- Upload, download, API call
- Example: `upload_audio`, `download_result`

**Decision Node**
- Branching logic (if/then/else)
- Routes based on previous output
- Example: "Did transcription succeed?"

**Merge Node**
- Combines outputs from parallel paths
- Waits for all inputs before proceeding
- Example: Combine results from 3 parallel translations

### Execution States

```
QUEUED → RUNNING → COMPLETED (with status: SUCCESS/FAILED/CANCELLED)
				 ↘ FAILED → RETRYING → RUNNING → ...
```

## Getting Started

### Define a Simple Workflow

```php
use SparxStar\\IAtlas\\NodeEngine\\{
	Workflow, Node, NodeType, Edge, ExecutionState
};

// Define nodes
$uploadNode = new Node(
	id: 'upload',
	name: 'Upload Audio',
	type: NodeType::IO,
	config: ['endpoint' => 'tus://...']
);

$transcribeNode = new Node(
	id: 'transcribe',
	name: 'Transcribe',
	type: NodeType::PROCESS,
	config: ['language' => 'en-US']
);

$storeNode = new Node(
	id: 'store',
	name: 'Store Result',
	type: NodeType::PROCESS,
	config: ['ttl' => 86400]
);

// Define edges (dependencies)
$edges = [
	new Edge(from: 'upload', to: 'transcribe'),
	new Edge(from: 'transcribe', to: 'store'),
];

// Create workflow
$workflow = new Workflow(
	id: 'simple-transcription-flow',
	name: 'Simple Transcription',
	nodes: [$uploadNode, $transcribeNode, $storeNode],
	edges: $edges
);
```

### Execute the Workflow

```php
$executor = $container->get(WorkflowExecutor::class);

$execution = $executor->execute(
	workflow: $workflow,
	input: ['audioFile' => '/tmp/message.wav']
);

// Poll for completion
while ($execution->getState() === ExecutionState::RUNNING) {
	sleep(2);
	$execution = $executor->getExecution($execution->getId());
}

// Check result
if ($execution->getState() === ExecutionState::COMPLETED) {
	$result = $execution->getResult();
	echo $result['transcription'];
} else {
	echo "Workflow failed: " . $execution->getError();
}
```

### Advanced: Branching Workflow

```php
$decisionNode = new Node(
	id: 'check_confidence',
	name: 'Check Confidence',
	type: NodeType::DECISION,
	config: ['threshold' => 0.85]
);

// Branches based on decision outcome
$edges = [
	new Edge(from: 'transcribe', to: 'check_confidence'),
	new Edge(
		from: 'check_confidence',
		to: 'store',
		condition: 'confidence >= 0.85'
	),
	new Edge(
		from: 'check_confidence',
		to: 'manual_review',
		condition: 'confidence < 0.85'
	),
	new Edge(from: 'manual_review', to: 'store'),
];
```

## Key Concepts

### Workflow Definition is Data
Workflows are defined as data structures, not code:
- Easier to visualize
- Easier to modify
- Can be loaded from JSON/YAML
- Can be version-controlled

### Execution is Independent of Definition
The workflow definition doesn't run; it describes how to run:
- Same definition can execute multiple times
- Each execution has its own state
- Can pause/resume executions
- Can inspect mid-flight execution state

### Parallelism
Multiple paths can run in parallel:
```
		   ┌→ Translate to Spanish
Transcribe ┼→ Translate to French
		   └→ Translate to German
					↓
				Merge Results
```

### Error Handling
Nodes can specify retry policies:
```php
$transcribeNode->setRetry(
	maxAttempts: 3,
	backoff: 'exponential',  // 1s, 2s, 4s
	retryOn: [
		ErrorCode::NETWORK_TIMEOUT,
		ErrorCode::QUOTA_EXCEEDED,
	]
);
```

## Common Integration Patterns

### Pattern 1: Simple Sequential Workflow
```
Upload → Transcribe → Store → Notify
```

### Pattern 2: Error Handling
```
Transcribe →─→ Success → Store
		  └→ Failure → Retry → ...
```

### Pattern 3: Parallel Processing
```
Transcribe →─→ Translate to Spanish
		  ├→ Translate to French
		  └→ Translate to German
			   ↓
			Merge All
			   ↓
			  Store
```

### Pattern 4: Human-in-the-Loop
```
Auto Transcribe
	  ↓
   Quality Check
	  ├→ Good → Store
	  └→ Bad → Manual Review → Correction → Store
```

## Important Notes

### Versioning
NodeEngine follows semantic versioning. Breaking changes are rare.

### Performance
Workflows are lightweight:
- DAG definition: negligible overhead
- Execution: depends on node implementations
- Typical orchestration latency: <10ms

### State Persistence
Execution state is stored durably:
- Can survive service restarts
- Can be inspected long after completion
- Complete audit trail of state changes

### Constraints
- No cycles (DAG, not cyclic graph)
- Must have entry and exit nodes
- Nodes must be deterministic (given same input, same output)

## Related Services

- **[IAtlas (Parent)](../)** — NodeEngine is one component
- **[DVE (Voice Engine)](../../DVE/)** — Uses NodeEngine for transcription workflows
- **[Dictionary](../Dictionary/)** — Defines node types and states

## Support & Questions

- **How do I define a workflow?** See [Getting Started](#getting-started) section
- **Workflow best practices?** Email `architecture@starisian.tech`
- **Performance tuning?** Email `ops@starisian.tech`

---

**Orchestration Engine** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
