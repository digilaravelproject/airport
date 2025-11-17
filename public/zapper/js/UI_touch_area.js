const TouchArea = ((element, callback) => {

  const LONG_PRESS_TIME = 500;
  const LONG_PRESS_MAX_DISTANCE = 20;

  class TouchAreaImpl {

    element = null;
    listeners = [];
    feedbackNodes = {};
    touchDownTime = 0;
    touchDownPosition = { x: 0, y: 0 };
    couldBeLongPress = false;
    isMouseDown = false;

    constructor(element) {
      this.element = element;
      this.element.addEventListener('pointerdown', this.onPointerDown.bind(this));
      this.element.addEventListener('pointermove', this.onPointerMove.bind(this));
      this.element.addEventListener('pointerup', this.onPointerUp.bind(this));
    }

    onPointerDown(event) {
      if (event.pointerType === 'mouse') {
        this.isMouseDown = true;
      }
      this.element.classList.add('touch-area__pressed');
      this.createFeedbackNode(event);
      this.touchDownTime = new Date().getTime();
      this.couldBeLongPress = true;
      this.touchDownPosition = { x: event.clientX, y: event.clientY };
    }

    onPointerMove(event) {
      if (event.pointerType === 'mouse' && !this.isMouseDown) {
        return;
      }
      const touchMovePosition = { x: event.clientX, y: event.clientY };
      const distance = Math.sqrt(
        Math.pow(touchMovePosition.x - this.touchDownPosition.x, 2) +
        Math.pow(touchMovePosition.y - this.touchDownPosition.y, 2)
      );
      if (distance > LONG_PRESS_MAX_DISTANCE) {
        this.couldBeLongPress = false;
        this.removeFeedbackNode(event.pointerId);
      }
    }

    onPointerUp(event) {
      if (event.pointerType === 'mouse') {
        this.isMouseDown = false;
      }
      const touchUpTime = new Date().getTime();
      const isLongPress = touchUpTime - this.touchDownTime > LONG_PRESS_TIME && this.couldBeLongPress;
      if (isLongPress) {
        this.listeners.forEach(listener => listener());
      }
      this.removeFeedbackNode(event.pointerId);
    }

    addEventListener(eventName, callback) {
      if (eventName !== 'longpress') {
        console.warn("TouchArea only supports 'longpress' event")
        return;
      }
      this.listeners.push(callback);
    }

    removeEventListener(eventName, callback) {
      if (eventName !== 'longpress') {
        console.warn("TouchArea only supports 'longpress' event")
        return;
      }
      const index = this.listeners.indexOf(callback);
      if (index !== -1) {
        this.listeners.splice(index, 1);
      }
    }

    createFeedbackNode(event) {
      const feedbackNode = document.createElement('div');
      feedbackNode.classList.add('feedback');
      feedbackNode.style.left = `${event.clientX}px`;
      feedbackNode.style.top = `${event.clientY}px`;
      feedbackNode.addEventListener('transitionend', () => {
        feedbackNode.remove();
        if (this.feedbackNodes[event.pointerId] === feedbackNode) {
          delete this.feedbackNodes[event.pointerId];
        }
      });
      this.element.appendChild(feedbackNode);
      const existingPointerNode = this.feedbackNodes[event.pointerId];
      this.feedbackNodes[event.pointerId] = feedbackNode;
      if (existingPointerNode) {
        existingPointerNode.classList.add('feedback__released');
      }
    }

    removeFeedbackNode (pointerId) {
      const pointerNode = this.feedbackNodes[pointerId];
      if (pointerNode) {
        pointerNode.classList.add('feedback__released');
      }
    }
  }
  
  return TouchAreaImpl;
})();