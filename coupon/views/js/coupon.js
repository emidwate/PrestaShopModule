const containerElem = document.querySelector('.coupon-container')
const exitBtn = document.querySelector('.coupon-exit-btn')

exitBtn.addEventListener('click', () => {
    containerElem.classList.remove('active-coupon')
})

function secondsToMiliseconds(timeInSeconds) {
    return timeInSeconds * 1000
}

let displayDurationNum = Number(displayDurationUserTime) 
let intervalDurationNum = Number(intervalDurationUserTime) 

// coupon display duration to the customer
let displayDuration = isNaN(displayDurationNum) || displayDurationNum === 0 
                    ? 15000 
                    : secondsToMiliseconds(displayDurationNum)     

// time pause between each coupon display
let intervalDuration = isNaN(intervalDurationNum) || intervalDurationNum === 0 
                    ? 35000 
                    : secondsToMiliseconds(intervalDurationNum) 

// Hides and displays coupon to the user 
function toggleCoupon() {
    containerElem.classList.add('active-coupon')
    setTimeout(() => {
        containerElem.classList.remove('active-coupon')
    }, displayDuration)
}

window.addEventListener('load', () => {
    setTimeout(() => {
        toggleCoupon()
        setInterval(() => {
            toggleCoupon()
        }, intervalDuration + displayDuration)
    }, intervalDuration)
})

const showCodeBtnElem = document.querySelector('.coupon-show-code-btn')
const clipBoardStatusMessage = document.querySelector('.clipboard-message')

showCodeBtnElem.addEventListener('click', () => {
    navigator.clipboard.writeText(discountCode)
    showCodeBtnElem.textContent = discountCode
    clipBoardStatusMessage.textContent = 'Copied'
    setTimeout(() => {
        clipBoardStatusMessage.textContent = ''
        showCodeBtnElem.textContent = 'Show code'
    }, displayDuration);
})