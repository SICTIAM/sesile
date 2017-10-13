import React, { Component } from 'react'
import { func, number } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'
import { AccordionItem } from '../_components/AdminUI'
import DraggablePosition from '../_components/DraggablePosition'
import { Button, Select } from '../_components/Form'

class CollectiviteSignature extends Component {

    static contextTypes = {
        t: func
    }

    static propTypes = {
        handleChange: func.isRequired,
        ordonnees_signature: number,
        abscisses_signature: number
    }

    static defaultProps = {
        ordonnees_signature: 10,
        abscisses_signature: 10,
        page_signature: ''
    }

    optionsPageSignature = [
        {value:0, text: this.context.t('admin.collectivite.first_page')},
        {value:1, text: this.context.t('admin.collectivite.last_page')}
    ]

    validationRules = {
        page_signature: "required",
        ordonnees_signature: "required",
        abscisses_signature: "required"
    }

    handleChangeDeltaPosition = (position) => {
        this.props.handleChange("abscisses_signature", position.x)
        this.props.handleChange("ordonnees_signature", position.y)
    }

    saveSignature = () => {
        const { id, page_signature, ordonnees_signature, abscisses_signature, putCollectivite } = this.props
        const fields = { page_signature, ordonnees_signature, abscisses_signature }
        const validation = new Validator(fields, this.validationRules)
        if(validation.passes()) if(id) putCollectivite(id, fields)
    }
    
    render() {
        const { t } = this.context
        const { id, page_signature, abscisses_signature, ordonnees_signature, handleChange, editState, putCollectivite } = this.props
        const ListPageSignature = this.optionsPageSignature.map(option => <option key={option.value} value={option.value}>{option.text}</option>)
        return (
            <AccordionItem title={t('admin.collectivite.signature_location')}>
                <div className="medium-6 cell">
                    <div className="grid-x grid-padding-y">
                        <Select id="page_signature"
                                value={page_signature} 
                                className={"medium-12 cell"}
                                label={t('common.label.page')}
                                onChange={handleChange}>
                            {ListPageSignature}
                        </Select>
                    </div>
                </div>
                <div className="medium-6 cell">
                    <DraggablePosition  className="cell medium-6"
                                        label="SIGNATURE"
                                        helpText={t('admin.collectivite.draggable_help_text')}
                                        style={{height: '300px', width: '245px', position: 'relative', overflow: 'auto', padding: '0'}}
                                        position={{x: abscisses_signature, y: ordonnees_signature}}
                                        handleChange={this.handleChangeDeltaPosition}/>
                </div>
                {(id) && 
                <Button id="submit-infos"
                        className="cell medium-12"
                        classNameButton="float-right"
                        onClick={this.saveSignature}
                        disabled={!editState}
                        labelText={t('common.button.edit_save')}/>}
            </AccordionItem>
        )
    }
}

export default translate(['sesile'])(CollectiviteSignature)