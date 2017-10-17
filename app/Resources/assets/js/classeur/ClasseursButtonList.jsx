import React, { Component } from 'react'
import { object, array }from 'prop-types'
import renderIf from 'render-if'

class ClasseursButtonList extends Component {

    render () {

        const { classeur, classeurs } = this.props



        return (

            <div className="grid-x">
                {
                    (
                        classeur && classeur.validable
                        || classeurs && !classeurs.filter(classeur => !classeur.validable).length
                    ) &&
                    <ButtonValid/>
                }

                {
                    (
                        classeur && classeur.signable_and_last_validant
                        || classeurs && !classeurs.filter(classeur => !classeur.signable_and_last_validant).length
                    ) &&
                    <ButtonSign/>
                }


                {
                    (
                        classeur && classeur.retractable
                        || classeurs && !classeurs.filter(classeur => !classeur.retractable).length
                    ) &&
                    <ButtonRevert/>
                }


                {
                    (
                        classeur && classeur.status === 3
                        || classeurs && !classeurs.filter(classeur => classeur.status !== 3).length > 0
                    ) &&
                    <ButtonRefus/>
                }

                {
                    (
                        classeur && classeur.comment
                        || classeurs && !classeurs.filter(classeur => !classeur.comment).length > 0
                    ) &&
                    <ButtonComment/>
                }


            </div>
        )
    }
}

ClasseursButtonList.PropTypes = {
    classeur: object,
    classeurs: array
}

export default ClasseursButtonList

const ButtonValid = () => {
    return(
        <div className="cell auto"><a href="#" className="btn-valid"></a></div>
    )
}
const ButtonSign = () => {
    return(
        <div className="cell auto"><a href="#" className="btn-sign"></a></div>
    )
}
const ButtonRevert = () => {
    return(
        <div className="cell auto"><a href="#" className="btn-revert"></a></div>
    )
}
const ButtonRefus = () => {
    return(
        <div className="cell auto"><a href="#" className="btn-refus"></a></div>
    )
}
const ButtonComment = () => {
    return(
        <div className="cell auto"><a href="#" className="btn-comment"></a></div>
    )
}