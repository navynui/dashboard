# Weekly Popular GGUF Models for Local Inference (16GB VRAM)

Based on current testing and community research, here are the most popular GGUF models that fit within 16GB VRAM constraints:

## Dense Models

### **Gemma-4-E4B-it-Q4_K_M.gguf**
- **VRAM Estimation**: ~8 GB
- **Speed**: 35.3 tokens/second (best speed)
- **Status**: ✅ BEST SPEED 
- **Why it's popular**: 
   - Small footprint with excellent performance
   - Good balance of speed and quality
   - Leaves VRAM headroom for context windows
   - Highly recommended for users seeking maximum throughput

### **Qwen3.6-28B-A3B-Q4_K_M.gguf** (Recommended)
- **VRAM Estimation**: ~15 GB (MoE with active params)
- **Speed**: 30.7 tokens/second
- **Status**: ✅ RECOMMENDED 
- **Why it's popular**: 
   - Balanced performance and intelligence
   - Good for complex tasks requiring reasoning
   - MoE architecture provides better quality without exceeding VRAM limits

## Mixture-of-Experts (MoE) Models 

### **Qwen3.6-35B-A3B-IQ3_S.gguf**
- **VRAM Estimation**: ~17 GB (MoE with 3.5B active params)
- **Speed**: 32.8 tokens/second
- **Status**: ✅ HIGH PERFORMER 
- **Why it's popular**:
   - Pushes VRAM limits but delivers excellent performance
   - MoE design allows for good reasoning capabilities
   - Fast despite being a large model
   - IQ3_S variant shows better speed than Q4_K_M in some cases

### **Qwen3.6-28B-A3B-Q4_K_M.gguf** 
- **VRAM Estimation**: ~15 GB (MoE with 3.5B active params)
- **Speed**: 30.7 tokens/second
- **Status**: ✅ RECOMMENDED 
- **Why it's popular**:  
   - Good balance of size and performance
   - Excellent for complex reasoning tasks
   - Better VRAM fit than dense models with similar capability

## Key Insights from Testing on P100 (16GB)

Based on the test results in `/home/nui/.hermes/skills/mlops/gguf-model-testing/references/p100_test_results.md`:

1. **MoE Models are the Sweet Spot**: MoE models that fit comfortably (<15 GB) perform well on P100, with Qwen3.6-28B-A3B-Q4_K_M being a prime example
2. **VRAM Estimation is Critical**: For MoE models:
   - Active parameters matter more than total parameters 
   - A 28B/3.5B model at Q4_K_M = ~1.75GB VRAM, not 7.5GB as dense would suggest
3. **IQ Variants Can Outperform**: IQ3_S can outperform standard quantizations on MoE models

## Recommendations for 16GB VRAM Systems:

### Dense Models (Best for Speed):
- Gemma-4-E4B-it-Q4_K_M: Highest throughput, smallest footprint
- Qwen3.6-28B-A3B-Q4_K_M: Good balance of quality and speed

### MoE Models (Best for Intelligence):
- Qwen3.6-35B-A3B-IQ3_S: Best performance within limits
- Qwen3.6-28B-A3B-Q4_K_M: Good trade-off between intelligence and VRAM usage

## Model Selection Criteria:

1. **VRAM Budget**: Models under 15GB are preferred for full GPU performance (above 15GB risk CPU offloading)
2. **Task Type**: 
   - For speed: E4B models
   - For reasoning: MoE models with active params ~3.5B or less
3. **Quantization Preference**: Q4_K_M is standard, IQ variants like IQ3_S show better performance in some cases

## Testing Approach Used:
The testing used a 5-round benchmark on P100 hardware (Tesla P100 PCIE 16GB) with the following criteria:
- Benchmark rounds: Knowledge QA, Technical Reasoning, Code Generation, Abstract Reasoning, Creative Writing
- Context window tested up to ~4096 tokens
- Performance measured in tokens per second (t/s)