// Performance monitoring utilities for checkout optimization

class PerformanceMonitor {
  constructor() {
    this.metrics = {
      startTime: null,
      milestones: [],
      cacheHits: 0,
      cacheMisses: 0,
      apiCalls: 0,
      errors: 0
    };
  }

  start() {
    this.metrics.startTime = performance.now();
    console.log('🚀 Performance monitoring started');
  }

  markMilestone(name) {
    const elapsed = performance.now() - this.metrics.startTime;
    this.metrics.milestones.push({ name, elapsed });
    console.log(`⏱️ ${name}: ${elapsed.toFixed(2)}ms`);
  }

  end() {
    const totalTime = performance.now() - this.metrics.startTime;
    console.log('📊 Performance Summary:', {
      totalTime: `${totalTime.toFixed(2)}ms`,
      milestones: this.metrics.milestones,
      cacheHits: this.metrics.cacheHits,
      cacheMisses: this.metrics.cacheMisses,
      apiCalls: this.metrics.apiCalls,
      errors: this.metrics.errors
    });
  }

  incrementCacheHit() {
    this.metrics.cacheHits++;
  }

  incrementCacheMiss() {
    this.metrics.cacheMisses++;
  }

  incrementApiCall() {
    this.metrics.apiCalls++;
  }

  incrementError() {
    this.metrics.errors++;
  }
}

// Checkout page performance monitoring
export const checkoutPerformance = {
  monitor: new PerformanceMonitor(),
  
  start() {
    this.monitor.start();
  },
  
  markMilestone(name) {
    this.monitor.markMilestone(name);
  },
  
  end() {
    this.monitor.end();
  }
};

// Shipping fee calculation performance monitoring
export const shippingPerformance = {
  calculations: [],
  currentCalculation: null,
  
  startCalculation() {
    const startTime = performance.now();
    this.currentCalculation = {
      startTime,
      sellerId: null,
      cacheHit: false,
      apiCall: false,
      error: false
    };
    return startTime;
  },
  
  endCalculation(startTime) {
    if (!this.currentCalculation) return;
    
    const duration = performance.now() - startTime;
    this.currentCalculation.duration = duration;
    this.calculations.push(this.currentCalculation);
    
    console.log(`📦 Shipping calculation completed:`, {
      sellerId: this.currentCalculation.sellerId,
      duration: `${duration.toFixed(2)}ms`,
      cacheHit: this.currentCalculation.cacheHit,
      apiCall: this.currentCalculation.apiCall,
      error: this.currentCalculation.error
    });
    
    this.currentCalculation = null;
  },
  
  cacheHit() {
    if (this.currentCalculation) {
      this.currentCalculation.cacheHit = true;
      checkoutPerformance.monitor.incrementCacheHit();
    }
  },
  
  cacheMiss() {
    if (this.currentCalculation) {
      this.currentCalculation.cacheMiss = true;
      checkoutPerformance.monitor.incrementCacheMiss();
    }
  },
  
  apiCall() {
    if (this.currentCalculation) {
      this.currentCalculation.apiCall = true;
      checkoutPerformance.monitor.incrementApiCall();
    }
  },
  
  error() {
    if (this.currentCalculation) {
      this.currentCalculation.error = true;
      checkoutPerformance.monitor.incrementError();
    }
  },
  
  getSummary() {
    const totalCalculations = this.calculations.length;
    const totalTime = this.calculations.reduce((sum, calc) => sum + calc.duration, 0);
    const avgTime = totalCalculations > 0 ? totalTime / totalCalculations : 0;
    const cacheHitRate = this.calculations.filter(c => c.cacheHit).length / totalCalculations;
    
    return {
      totalCalculations,
      totalTime: `${totalTime.toFixed(2)}ms`,
      averageTime: `${avgTime.toFixed(2)}ms`,
      cacheHitRate: `${(cacheHitRate * 100).toFixed(1)}%`,
      errors: this.calculations.filter(c => c.error).length
    };
  }
};

// Utility for measuring specific operations
export const measureOperation = async (name, operation) => {
  const startTime = performance.now();
  try {
    const result = await operation();
    const duration = performance.now() - startTime;
    console.log(`⚡ ${name}: ${duration.toFixed(2)}ms`);
    return result;
  } catch (error) {
    const duration = performance.now() - startTime;
    console.error(`❌ ${name} failed after ${duration.toFixed(2)}ms:`, error);
    throw error;
  }
};

// Utility for batch operations with progress tracking
export const batchOperation = async (items, operation, batchSize = 5) => {
  const results = [];
  const totalBatches = Math.ceil(items.length / batchSize);
  
  for (let i = 0; i < items.length; i += batchSize) {
    const batch = items.slice(i, i + batchSize);
    const batchNumber = Math.floor(i / batchSize) + 1;
    
    console.log(`🔄 Processing batch ${batchNumber}/${totalBatches} (${batch.length} items)`);
    
    const batchStart = performance.now();
    const batchResults = await Promise.all(batch.map(operation));
    const batchDuration = performance.now() - batchStart;
    
    console.log(`✅ Batch ${batchNumber} completed in ${batchDuration.toFixed(2)}ms`);
    results.push(...batchResults);
  }
  
  return results;
}; 