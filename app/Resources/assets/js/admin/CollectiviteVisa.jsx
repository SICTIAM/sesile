import React, { Component } from 'react'
import { func, string, number } from 'prop-types'
import { translate } from 'react-i18next'
import { TwitterPicker } from 'react-color'
import Validator from 'validatorjs'
import { AccordionItem } from '../_components/AdminUI'
import DraggablePosition from '../_components/DraggablePosition'
import InputValidation from '../_components/InputValidation'
import { Button } from '../_components/Form'

class CollectiviteVisa extends Component {

    static contextTypes = {
        t: func
    }

    static propTypes = {
        handleChange: func.isRequired,
        titre_visa: string,
        ordonnees_visa: number,
        abscisses_visa: number
    }

    static defaultProps = {
        ordonnees_visa: 10,
        abscisses_visa: 10,
        titre_visa: '',
        couleur_visa: ''
    }

    validationRules = {
        titre_visa: "required|max:250",
        ordonnees_visa: "required",
        abscisses_visa: "required"
    }

    customErrorMessages = {
        titre_visa: {required: this.context.t('admin.collectivite.error.visa_title')}
    }

    handleChangeDeltaPosition = (position) => {
        this.props.handleChange("abscisses_visa", position.x)
        this.props.handleChange("ordonnees_visa", position.y)
    }

    handleChangeColor = (color) => this.props.handleChange("couleur_visa", color.hex)

    saveVisa = () => {
        const { id, titre_visa, ordonnees_visa, abscisses_visa, couleur_visa, putCollectivite } = this.props
        const fields = { titre_visa, ordonnees_visa, abscisses_visa, couleur_visa }
        const validation = new Validator(fields, this.validationRules)
        if(validation.passes()) if(id) putCollectivite(id, fields)
    }
    
    render() {
        const { t } = this.context
        const { id, abscisses_visa, ordonnees_visa, titre_visa, handleChange, couleur_visa, editState } = this.props
        return (
            <AccordionItem title={t('admin.collectivite.visa_location')}>
                <div className="medium-6 cell">
                    <div className="grid-x grid-padding-y">
                        <InputValidation    id="titre_visa"
                                            type="text"  
                                            className={"medium-12 cell"}
                                            labelText={t('common.label.title')}
                                            value={titre_visa} 
                                            onChange={handleChange}
                                            validationRule={this.validationRules.titre_visa}
                                            customErrorMessages={this.customErrorMessages.titre_visa}
                                            placeholder={t('admin.collectivite.placeholder_type_visa_title')}/>
                        <TwitterPicker  color={ couleur_visa || '#000000' }
                                        onChangeComplete={ this.handleChangeColor }
                                        colors={['#FF6900', '#FCB900', '#7BDCB5', '#00D084', '#8ED1FC', '#0693E3', '#ABB8C3', '#000000', '#9900EF', '#00008B']} />
                    </div>
                </div>
                <div className="medium-6 cell">
                    <DraggablePosition  className="cell medium-6"
                                        label="VISA"
                                        helpText={t('admin.collectivite.draggable_help_text')}
                                        labelColor={couleur_visa}
                                        position={{x: abscisses_visa, y: ordonnees_visa}}
                                        handleChange={this.handleChangeDeltaPosition}/>
                </div>
                {(id) && 
                <Button id="submit-infos"
                        className="cell medium-12"
                        classNameButton="float-right"
                        onClick={this.saveVisa}
                        disabled={!editState}
                        labelText={t('common.button.edit_save')}/>}
            </AccordionItem>
        )
    }
}

export default translate(['sesile'])(CollectiviteVisa)